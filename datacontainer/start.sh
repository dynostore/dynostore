#!/bin/sh

################################################################################
# Start the uvicorn service and the log watcher
################################################################################

LOG_FILE="/app/logs/kagio$DATA_CONTAINER_ID.log"

# Start the log watcher in the background 
logwatcher &

# Start the uvicorn server for the metadata service
gunicorn --reload --bind 0.0.0.0:80 app:app \
             --workers 1 --threads 2 \
             --access-logfile '-' --error-logfile '-' --log-level debug