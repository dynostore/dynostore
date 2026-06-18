#!/bin/sh

################################################################################
# Start the uvicorn service and the log watcher
################################################################################

# Start the log watcher in the background 
#logwatcher  2>&1 | tee /kagio/logs/metadata.log & # NOT NEEDED BY NOW, I LET IT IF WE ADD KAGIO IN THE FUTURE TO THE METADATA SERVER

# Start the uvicorn server for the metadata service
uvicorn main:app --reload --host 0.0.0.0 --port 80