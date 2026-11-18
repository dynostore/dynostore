#!/bin/bash
# Create the metadata schema.  The metadata service also does this on start-up;
# running it here is idempotent and fails loudly if the database is unreachable.
set -euo pipefail
COMPOSE=(docker compose -f "${DYNOSTORE_COMPOSE_FILE:-docker-compose.dev.yml}")
if [ -n "${DYNOSTORE_PROJECT:-}" ]; then COMPOSE+=(-p "$DYNOSTORE_PROJECT"); fi

for attempt in $(seq 1 60); do
  if "${COMPOSE[@]}" exec -T metadata_server python -c \
      "import models; from database import engine, Base; Base.metadata.create_all(bind=engine)" 2>/dev/null; then
    echo "Metadata schema ready"
    exit 0
  fi
  sleep 2
done
echo "Metadata database did not become ready" >&2
exit 1
