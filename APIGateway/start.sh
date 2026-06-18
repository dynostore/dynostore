#!/bin/sh

# Start the Log Watcher in the background
logwatcher  2>&1 | tee /app/logs/kagio.log &

# Start the Hypercorn server for the API Gateway

hypercorn -c hypercorn.toml main:app --reload --log-level debug