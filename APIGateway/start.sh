#!/bin/sh

# Start the Log Watcher in the background
if [ "$ENABLE_KAGIO" = "true" ]; then
    logwatcher &
fi

# Start the Hypercorn server for the API Gateway

hypercorn -c hypercorn.toml main:app --reload --log-level debug