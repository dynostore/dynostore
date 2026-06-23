#!/bin/sh

################################################################################
# Start the uvicorn service and the log watcher
################################################################################

LOG_FILE="/app/logs/kagio$DATA_CONTAINER_ID.log"

# Start the log watcher in the background 
if [ "$ENABLE_KAGIO" = "true" ]; then
    logwatcher &
fi

# Start the uvicorn server for the metadata service
gunicorn --reload --bind 0.0.0.0:80 app:app \
             --workers 1 --threads 2 \
             --access-logfile '-' --error-logfile '-' --log-level debug