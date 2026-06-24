import os
import sys
import time
import subprocess
import json
import random
import uuid
import requests

from dynostore.client import Client
from kagio.kagio import KAGIO

# Add benchmarks directory to path so it can find things if needed
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

def run_cmd(cmd, env_vars=None):
    env = os.environ.copy()
    if env_vars:
        env.update(env_vars)
    subprocess.run(cmd, shell=True, env=env, check=True)

def clean_system():
    print("Cleaning system (Monitor API)...")
    try:
        requests.post("http://localhost:8092/api/cleanup/data")
        requests.post("http://localhost:8092/api/cleanup/metadata")
        requests.post("http://localhost:8092/api/cleanup/logs")
    except Exception as e:
        print("Cleanup error:", e)
    
    print("Resetting KAGIO Graph...")
    try:
        run_cmd("cd /home/domizzi/Documents/GitHub/dynostore-knowledgegraphs && bash restart_and_deploy.sh")
    except Exception as e:
        print("Error resetting KAGIO:", e)
    
    # Because Docker containers write logs/objects as root, clearing them locally via Python 
    # results in Permission Denied. We use a temporary root Docker container to bypass this.
    base_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    
    logs_dir = os.path.join(base_dir, "datacontainer", "code", "logs")
    if os.path.exists(logs_dir):
        try:
            run_cmd(f"docker run --rm -v {logs_dir}:/logs alpine sh -c 'rm -rf /logs/*'")
        except Exception as e:
            print(f"Error clearing logs: {e}")
            
    for i in range(1, 11):
        obj_dir = os.path.join(base_dir, "datacontainer", f"objects{i}")
        if os.path.exists(obj_dir):
            try:
                run_cmd(f"docker run --rm -v {obj_dir}:/obj alpine sh -c 'rm -rf /obj/*'")
            except Exception as e:
                print(f"Error clearing objects{i}: {e}")

def restart_cluster(enable_kagio, enable_replicator):
    print(f"\n---> Restarting Cluster: KAGIO={enable_kagio}, REPLICATOR={enable_replicator} <---")
    env = {
        "ENABLE_KAGIO": str(enable_kagio).lower(),
        "ENABLE_REPLICATOR": str(enable_replicator).lower()
    }
    # Force recreate apigateway and datacontainers
    cmd = "docker compose -f ../docker-compose.dev.yml up -d --force-recreate apigateway metadata_server datacontainer1 datacontainer2 datacontainer3 datacontainer4 datacontainer5 datacontainer6 datacontainer7 datacontainer8 datacontainer9 datacontainer10"
    run_cmd(cmd, env)
    print("Waiting 15 seconds for services to become healthy...")
    time.sleep(15)

def get_pageranks(kagio_host):
    try:
        KAGIO_API_KEY = os.getenv("KAGIO_API_KEY", "my_token")
        KAGIO_FOXX_URL = os.getenv("KAGIO_FOXX_URL", "http://10.18.173.209:8529/_db/_system/kagio")
        KAGIO_FOXX_DB = os.getenv("KAGIO_FOXX_DB", "_system")
        kagio_client = KAGIO(base_url=kagio_host, foxx_url=KAGIO_FOXX_URL, foxx_db=KAGIO_FOXX_DB, api_key=KAGIO_API_KEY)
        
        pr_list = kagio_client.centrality.data_containers_page_rank()
        
        # Determine if it's wrapped in a 'data' attribute or a direct list
        if hasattr(pr_list, 'data'):
            items = pr_list.data
        elif isinstance(pr_list, list):
            items = pr_list
        else:
            items = []
            
        result = {}
        for dc in items:
            pr = dc.get("pagerank", 0.0)
            dc_id = str(dc.get("id", dc.get("vertex", "")))
            
            # Extract number from "dc-0", "datacontainer-1", etc.
            num_str = ''.join(filter(str.isdigit, dc_id))
            if num_str:
                # Assuming dc-0 maps to datacontainer1 if it's 0-indexed, but in dynostore we usually use datacontainerX
                # Let's map it safely
                num = int(num_str)
                # In many cases dc-0 = datacontainer1. Let's adjust if 0-indexed.
                if "dc-0" in dc_id or num == 0:
                    pass # We will handle translation below
                
                # The evaluate_scenarios.py expects keys like "datacontainer1"
                # If Kagio uses dc-0 for datacontainer1, we add 1. If it uses dc-1 for datacontainer1, we don't.
                # Given user's output: dc-0, dc-1... dc-9. Since dynostore uses datacontainer1 to 10, it's 0-indexed!
                # So dc-0 -> datacontainer1, dc-1 -> datacontainer2
                mapped_name = f"datacontainer{num + 1}" if num < 10 and any(f"dc-{i}" == dc_id for i in range(10)) else f"datacontainer{num}"
                result[mapped_name] = pr
            else:
                result[dc_id] = pr
        return result
    except Exception as e:
        print(f"Error fetching KAGIO PageRanks: {e}")
    return {}

def collect_metrics(kagio_host):
    metrics = {}
    for i in range(1, 11):
        dc_name = f"datacontainer{i}"
        
        # 1. Requests
        log_file = f"../datacontainer/code/logs/datacontainer-{i}.log"
        requests_count = 0
        if os.path.exists(log_file):
            with open(log_file) as f:
                content = f.read()
                requests_count = sum(1 for line in content.split('\n') if "DOWNLOAD" in line and "SUCCESS" in line)
                
        # 2. Storage & Objects
        obj_dir = f"../datacontainer/objects{i}/"
        obj_count = 0
        total_size = 0
        if os.path.exists(obj_dir):
            for root, dirs, files in os.walk(obj_dir):
                for f in files:
                    if f == ".gitkeep":
                        continue
                    fp = os.path.join(root, f)
                    if os.path.isfile(fp):
                        obj_count += 1
                        total_size += os.path.getsize(fp)
                        
        metrics[dc_name] = {
            "requests_attended": requests_count,
            "objects_count": obj_count,
            "storage_MB": round(total_size / (1024 * 1024), 2)
        }
        
    # 3. PageRanks
    pageranks = get_pageranks(kagio_host)
    for dc, data in metrics.items():
        data["pagerank"] = pageranks.get(dc, 0.0)
        
    return metrics

def run_scenario(scenario_name, enable_kagio, enable_replicator, num_objects=10, benchmark_reads=50):
    print(f"\n=======================================================")
    print(f" RUNNING SCENARIO: {scenario_name}")
    print(f"=======================================================")
    
    clean_system()
    restart_cluster(enable_kagio, enable_replicator)
    
    gateway_host = os.getenv("GATEWAY_HOST", "127.0.0.1:8070")
    kagio_host = os.getenv("KAGIO_HOST", "http://10.18.173.209:8080")
    catalog_name = "eval_catalog"
    client = Client(gateway_host)
    rnd = random.Random(42) # Deterministic workload
    client_regions = ["eu-west", "us-east", "us-west", "ap-south"]
    
    print("\n--- Phase 1: Ingestion & Indegree Generation ---")
    objects = []
    
    write_latencies = []
    read_latencies = []
    
    for i in range(num_objects):
        region = rnd.choice(client_regions)
        size_MB = rnd.randint(10, 50) # smaller size for faster evals
        size_B = size_MB * 1024**2
        obj_id = str(uuid.uuid4())
        
        print(f"[{i+1}/{num_objects}] Uploading {size_MB}MB for {obj_id} from {region}...")
        data = os.urandom(size_B)
        
        t0 = time.time()
        res = client.put(data=data, catalog=catalog_name, key=obj_id)
        t1 = time.time()
        
        if not res:
            print(f"Failed to upload {obj_id}")
            continue
            
        write_latencies.append(t1 - t0)

        # Target indegree based on some distribution
        data_type = rnd.randint(1, 10)
        if data_type <= 2:
            times = rnd.randint(5, 10)
        elif data_type <= 6:
            times = rnd.randint(3, 6)
        else:
            times = rnd.randint(0, 3)

        objects.append({
            "id": obj_id,
            "reads": times
        })

        print(f"  -> Performing {times} reads to generate baseline graph...")
        for _ in range(times):
            t0 = time.time()
            client.get(key=obj_id)
            t1 = time.time()
            read_latencies.append(t1 - t0)
            
    if enable_replicator:
        wait_time = 45
        print(f"\n--- Phase 2: Waiting {wait_time}s for automatic replication ---")
        time.sleep(wait_time)
    else:
        print("\n--- Phase 2: Replication disabled, skipping wait ---")

    print("\n--- Collecting PageRanks before benchmark ---")
    pr_before = get_pageranks(kagio_host)

    print("\n--- Phase 3: Benchmark Replicated Objects ---")
    # Identify top 25% objects by intended reads
    top_objects = sorted(objects, key=lambda x: x["reads"], reverse=True)
    top_25_count = max(1, int(len(top_objects) * 0.25))
    top_25 = top_objects[:top_25_count]
    
    print(f"Targeting top {top_25_count} objects...")
    
    t_start = time.time()
    for idx, obj in enumerate(top_25):
        obj_id = obj["id"]
        print(f"[{idx+1}/{top_25_count}] Benchmarking {obj_id} ({benchmark_reads} reads)...")
        
        for _ in range(benchmark_reads):
            t0 = time.time()
            client.get(key=obj_id)
            t1 = time.time()
            read_latencies.append(t1 - t0)
            
    t_end = time.time()
    perf_time = round(t_end - t_start, 2)
    print(f"\nBenchmark Performance Time: {perf_time} seconds")
    
    print("\n--- Phase 4: Collecting Metrics ---")
    metrics = collect_metrics(kagio_host)
    
    # Calculate PageRank variation
    for dc, data in metrics.items():
        data["pagerank_before"] = pr_before.get(dc, 0.0)
        data["pagerank_after"] = data.pop("pagerank")  # Rename for clarity
        data["pagerank_variation"] = data["pagerank_after"] - data["pagerank_before"]
    
    result = {
        "scenario": scenario_name,
        "performance_time_seconds": perf_time,
        "container_metrics": metrics,
        "user_latencies": {
            "write_latencies_seconds": write_latencies,
            "read_latencies_seconds": read_latencies
        }
    }
    
    print("\nMetrics Summary:")
    print(json.dumps(result, indent=2))
    return result


def main():
    results = []
    
    # Config for quick evaluation
    num_objects = 5
    benchmark_reads = 30
    
    # 1. Utilization Factor Load Balancer
    res1 = run_scenario("Utilization Factor LB (No Replication)", enable_kagio=False, enable_replicator=False, num_objects=num_objects, benchmark_reads=benchmark_reads)
    results.append(res1)
    
    # 2. PageRank Load Balancer without replication
    res2 = run_scenario("PageRank LB (No Replication)", enable_kagio=True, enable_replicator=False, num_objects=num_objects, benchmark_reads=benchmark_reads)
    results.append(res2)
    
    # 3. PageRank Load Balancer with replication
    res3 = run_scenario("PageRank LB (With Replication)", enable_kagio=True, enable_replicator=True, num_objects=num_objects, benchmark_reads=benchmark_reads)
    results.append(res3)

    
    print("\n=======================================================")
    print(" EVALUATION COMPLETE")
    print("=======================================================")
    with open("evaluation_report.json", "w") as f:
        json.dump(results, f, indent=2)
    print("Report saved to evaluation_report.json")

if __name__ == "__main__":
    main()
