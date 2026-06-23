import os
import time
import random
import requests
import json
import argparse
import uuid
from dynostore.client import Client
from kagio.kagio import KAGIO

def get_pageranks(api_url):
    try:
        KAGIO_API_KEY = os.getenv("KAGIO_API_KEY", "my_token")
        KAGIO_FOXX_URL = os.getenv("KAGIO_FOXX_URL", "http://192.168.1.116:8529/_db/_system/kagio")
        KAGIO_FOXX_DB = os.getenv("KAGIO_FOXX_DB", "_system")
        kagio_client = KAGIO(base_url=api_url, foxx_url=KAGIO_FOXX_URL, foxx_db=KAGIO_FOXX_DB, api_key=KAGIO_API_KEY)
        
        pr_list = kagio_client.centrality.data_containers_page_rank()
        if pr_list:
            return pr_list
    except Exception as e:
        print(f"Error fetching KAGIO PageRanks: {e}")
    return {}

def main():
    parser = argparse.ArgumentParser(description="Benchmark reading top 25% objects N times")
    parser.add_argument("--objects", type=int, default=100, help="Total number of objects to ingest")
    parser.add_argument("--reads", type=int, default=50, help="Number of times (n) to read each of the top 25% objects")
    args = parser.parse_args()

    gateway_host = os.getenv("GATEWAY_HOST", "127.0.0.1:8070")
    kagio_host = os.getenv("KAGIO_HOST", "http://192.168.1.116:8080")
    catalog_name = "benchmark_catalog"

    print(f"Initializing DynoStore client ({gateway_host})...")
    client = Client(gateway_host)

    rnd = random.Random()
    client_regions = ["eu-west", "us-east", "us-west", "ap-south"]

    objects = []
    
    total_objects = args.objects
    n_reads = args.reads

    # print(f"\n--- Phase 1: Ingestion & Indegree Generation ({total_objects} objects) ---")
    # for i in range(total_objects):
    #     region = rnd.choice(client_regions)
    #     size_MB = rnd.randint(128, 1024)
    #     size_B = size_MB # scaled down for test environments, or use size_MB * 1024**2
    #     obj_id = str(uuid.uuid4())
        
    #     print(f"[{i+1}/{total_objects}] Generating {size_MB}MB for {obj_id}...")
    #     data = os.urandom(size_B)
        
    #     print(f"[{i+1}/{total_objects}] Uploading {obj_id} from {region}...")
    #     res = client.put(data=data, catalog=catalog_name, key=obj_id)
    #     if not res:
    #         print(f"Failed to upload {obj_id}")
    #         continue

    #     # Initial random reads to build baseline indegree
    #     data_type = rnd.randint(1, 10)
    #     if data_type <= 2:
    #         times = rnd.randint(70, 100)
    #     elif data_type <= 6:
    #         times = rnd.randint(10, 70)
    #     else:
    #         times = rnd.randint(0, 10)

    #     objects.append({
    #         "id": obj_id,
    #         "reads": times
    #     })

    #     for _ in range(times):
    #         client.get(key=obj_id)
            
    # print("\n--- Phase 2: Wait for Automatic Replication ---")
    # pr_before = get_pageranks(kagio_host)
    # print("PageRanks before replication:")
    # print(pr_before)
    
    # wait_time = 45
    #print(f"Waiting {wait_time} seconds for the replicator daemon to process top objects...")
    #time.sleep(wait_time)
    
    print(f"\n--- Phase 3: Benchmark Replicated Objects (Top 25% read {n_reads} times) ---")
    
    try:
        KAGIO_API_KEY = os.getenv("KAGIO_API_KEY", "my_token")
        KAGIO_FOXX_URL = os.getenv("KAGIO_FOXX_URL", "http://192.168.1.116:8529/_db/_system/kagio")
        KAGIO_FOXX_DB = os.getenv("KAGIO_FOXX_DB", "_system")
        kagio_client = KAGIO(base_url=kagio_host, foxx_url=KAGIO_FOXX_URL, foxx_db=KAGIO_FOXX_DB, api_key=KAGIO_API_KEY)
        
        indegree_data = kagio_client.centrality.metadata_indegree()
        
        objects = []
        seen = set()
        for item in indegree_data:
            m_id = item.get("metadata_id", "")
            if m_id.startswith("metadata_"):
                m_id = m_id[len("metadata_"):]
            base_id = m_id.split("_")[0]
            if base_id and base_id not in seen:
                seen.add(base_id)
                objects.append({
                    "id": base_id,
                    "reads": item.get("indegree", 0)
                })
    except Exception as e:
        print(f"Error fetching indegree from Kagio: {e}")

    top_objects = sorted(objects, key=lambda x: x["reads"], reverse=True)
    
    total_objects_found = len(top_objects)
    top_25_count = max(1, int(total_objects_found * 0.25))
    top_25 = top_objects[:top_25_count]
    
    print(f"Targeting the top {top_25_count} objects from {total_objects_found} unique KAGIO objects.")
    
    t_start_phase3 = time.time()
    print(top_25)
    for idx, obj in enumerate(top_25):
        obj_id = obj["id"]
        print(f"[{idx+1}/{top_25_count}] Reading top object {obj_id} (Initial indegree: {obj['reads']}) - {n_reads} times")
        
        t0 = time.time()
        for _ in range(n_reads):
            client.get(key=obj_id)
        t1 = time.time()
        
        print(f"  -> {n_reads} reads took {t1-t0:.2f} seconds")

    t_end_phase3 = time.time()
    print(f"\nPhase 3 total time: {t_end_phase3 - t_start_phase3:.2f} seconds")

    pr_after = get_pageranks(kagio_host)
    print("\nPageRanks after replication:")
    print(pr_after)

if __name__ == "__main__":
    main()
