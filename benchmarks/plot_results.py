import json
import os
import matplotlib.pyplot as plt
import numpy as np

def load_data(filepath="evaluation_report.json"):
    if not os.path.exists(filepath):
        print(f"Error: {filepath} not found. Please run evaluate_scenarios.py first.")
        return None
    with open(filepath, 'r') as f:
        return json.load(f)

def plot_performance(data):
    scenarios = [d['scenario'] for d in data]
    times = [d['performance_time_seconds'] for d in data]
    
    plt.figure(figsize=(10, 6))
    bars = plt.bar(scenarios, times, color=['#4C72B0', '#55A868', '#C44E52'])
    plt.title('Benchmark Performance Time per Scenario')
    plt.ylabel('Time (Seconds)')
    plt.xticks(rotation=15)
    
    # Add values on top of bars
    for bar in bars:
        yval = bar.get_height()
        plt.text(bar.get_x() + bar.get_width()/2, yval + (max(times)*0.01), f"{yval:.2f}s", ha='center', va='bottom')
        
    plt.tight_layout()
    plt.savefig('plot_performance.png')
    plt.close()

def plot_grouped_container_metric(data, metric_key, title, ylabel, filename):
    scenarios = [d['scenario'] for d in data]
    containers = [f"datacontainer{i}" for i in range(1, 11)]
    
    x = np.arange(len(containers))
    width = 0.25
    
    plt.figure(figsize=(14, 7))
    
    for i, scenario in enumerate(data):
        metrics = scenario['container_metrics']
        values = [metrics.get(c, {}).get(metric_key, 0) for c in containers]
        plt.bar(x + (i - 1) * width, values, width, label=scenario['scenario'])
        
    plt.title(title)
    plt.xlabel('Data Containers')
    plt.ylabel(ylabel)
    plt.xticks(x, [c.replace('datacontainer', 'DC-') for c in containers])
    plt.legend()
    plt.tight_layout()
    plt.savefig(filename)
    plt.close()

def plot_pagerank_variation(data):
    # Only plot for scenarios that actually have KAGIO enabled, though Scenario 1 now tracks it too.
    containers = [f"datacontainer{i}" for i in range(1, 11)]
    
    x = np.arange(len(containers))
    width = 0.25
    
    plt.figure(figsize=(14, 7))
    
    for i, scenario in enumerate(data):
        metrics = scenario['container_metrics']
        variations = [metrics.get(c, {}).get('pagerank_variation', 0) for c in containers]
        plt.bar(x + (i - 1) * width, variations, width, label=scenario['scenario'])
        
    plt.title('PageRank Variation per Data Container During Benchmark')
    plt.xlabel('Data Containers')
    plt.ylabel('Delta PageRank (After - Before)')
    plt.xticks(x, [c.replace('datacontainer', 'DC-') for c in containers])
    plt.axhline(0, color='black', linewidth=0.8, linestyle='--')
    plt.legend()
    plt.tight_layout()
    plt.savefig('plot_pagerank_variation.png')
    plt.close()

def plot_user_latencies(data):
    scenarios = [d['scenario'] for d in data]
    read_latencies = [d['user_latencies']['read_latencies_seconds'] for d in data]
    write_latencies = [d['user_latencies']['write_latencies_seconds'] for d in data]
    
    # Plot Read Latencies
    plt.figure(figsize=(10, 6))
    plt.boxplot(read_latencies)
    plt.xticks(range(1, len(scenarios) + 1), [s.replace(' ', '\n') for s in scenarios])
    plt.title('User Observed Read Latencies per Scenario')
    plt.ylabel('Latency (Seconds)')
    plt.tight_layout()
    plt.savefig('plot_read_latencies.png')
    plt.close()

    # Plot Write Latencies
    plt.figure(figsize=(10, 6))
    plt.boxplot(write_latencies)
    plt.xticks(range(1, len(scenarios) + 1), [s.replace(' ', '\n') for s in scenarios])
    plt.title('User Observed Write Latencies per Scenario')
    plt.ylabel('Latency (Seconds)')
    plt.tight_layout()
    plt.savefig('plot_write_latencies.png')
    plt.close()

def main():
    data = load_data()
    if not data:
        return
        
    print("Generating plots...")
    
    plot_performance(data)
    print(" -> Saved plot_performance.png")
    
    plot_grouped_container_metric(
        data, 
        'requests_attended', 
        'Number of Read Operations per Data Container', 
        'Read Operations', 
        'plot_read_operations.png'
    )
    print(" -> Saved plot_read_operations.png")
    
    plot_grouped_container_metric(
        data, 
        'storage_MB', 
        'Storage Utilization (MB) per Data Container', 
        'Storage (MB)', 
        'plot_storage_utilization.png'
    )
    print(" -> Saved plot_storage_utilization.png")
    
    plot_grouped_container_metric(
        data, 
        'objects_count', 
        'Number of Objects per Data Container', 
        'Objects Count', 
        'plot_objects_count.png'
    )
    print(" -> Saved plot_objects_count.png")
    
    plot_pagerank_variation(data)
    print(" -> Saved plot_pagerank_variation.png")
    
    plot_grouped_container_metric(
        data, 
        'pagerank_after', 
        'Final PageRank per Data Container', 
        'PageRank Score', 
        'plot_pagerank_final.png'
    )
    print(" -> Saved plot_pagerank_final.png")
    
    if 'user_latencies' in data[0]:
        plot_user_latencies(data)
        print(" -> Saved plot_read_latencies.png")
        print(" -> Saved plot_write_latencies.png")
    
    print("\nAll plots generated successfully!")

if __name__ == "__main__":
    main()
