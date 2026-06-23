import os
import time
import random
import requests
import json
from dynostore.client import Client
from kagio.kagio import KAGIO

def get_pageranks(api_url):
    try:
        KAGIO_API_KEY = os.getenv("KAGIO_API_KEY", "my_token")
        KAGIO_FOXX_URL = os.getenv("KAGIO_FOXX_URL", "http://192.168.1.116:8529/_db/_system/kagio")
        KAGIO_FOXX_DB = os.getenv("KAGIO_FOXX_DB", "_system")
        kagio_client = KAGIO(base_url=api_url, foxx_url=KAGIO_FOXX_URL, foxx_db=KAGIO_FOXX_DB, api_key=KAGIO_API_KEY)
        
        pr_list = kagio_client.centrality.data_containers_page_rank()
        print(pr_list)
        if pr_list:
            return pr_list
    except Exception as e:
        print(f"Error fetching KAGIO PageRanks: {e}")
    return {}

def main():
    gateway_host = os.getenv("GATEWAY_HOST", "127.0.0.1:80")
    kagio_host = os.getenv("KAGIO_HOST", "http://192.168.1.116:8080")
    catalog_name = "benchmark_catalog"

    print(f"Initializing DynoStore client ({gateway_host})...")
    client = Client(gateway_host)

    rnd = random.Random()
    client_regions = ["eu-west", "us-east", "us-west", "ap-south"]

    objects = []
    
    print("\n--- Phase 1: Ingestion & Indegree Generation ---")
    for i in range(100):
        region = rnd.choice(client_regions)
        size_MB = rnd.randint(128, 1024)
        size_B = size_MB #* 1024**2
        import uuid
        obj_id = str(uuid.uuid4())
        
        print(f"[{i+1}/10] Generating {size_MB}MB for {obj_id}...")
        data = os.urandom(size_B)
        
        print(f"[{i+1}/10] Uploading {obj_id} from {region}...")
        res = client.put(data=data, catalog=catalog_name, key=obj_id)
        if not res:
            print(res)
            print(f"Failed to upload {obj_id}")
            continue

        data_type = rnd.randint(1, 10)
        if data_type <= 2:
            times = rnd.randint(70, 100)
        elif data_type <= 6:
            times = rnd.randint(10, 70)
        else:
            times = rnd.randint(0, 10)

        objects.append({
            "id": obj_id,
            "reads": times
        })

        print(f"[{i+1}/10] Performing {times} reads to generate indegree for {obj_id}...")
        for _ in range(times):
            client.get(key=obj_id)
            
    print("\n--- Phase 2: Wait for Automatic Replication ---")
    pr_before = get_pageranks(kagio_host)
    print("PageRanks before replication:")
    print(json.dumps(pr_before, indent=2))
    
    wait_time = 45
    print(f"Waiting {wait_time} seconds for the 30-second replicator daemon to process top objects...")
    time.sleep(wait_time)
    
    
    print("\n--- Phase 3: Benchmark Replicated Objects ---")
    top_objects = sorted(objects, key=lambda x: x["reads"], reverse=True)
    
    for obj in top_objects[:30]:
        obj_id = obj["id"]

        data_type = rnd.randint(1, 10)
        if data_type <= 2:
            times = rnd.randint(70, 100)
        elif data_type <= 6:
            times = rnd.randint(10, 70)
        else:
            times = rnd.randint(0, 10)

        for _ in range(times):
            client.get(key=obj_id)

        

    pr_after = get_pageranks(kagio_host)
    print("\nPageRanks after replication:")
    print(json.dumps(pr_after, indent=2))
    

if __name__ == "__main__":
    main()
