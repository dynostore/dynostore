#!/bin/sh

# Start the Log Watcher in the background
logwatcher  2>&1 | tee /logwatcher.log &

# Start the Hypercorn server for the API Gateway

hypercorn -c hypercorn.toml main:app --reload --log-level debug