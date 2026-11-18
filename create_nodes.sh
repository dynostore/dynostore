#!/bin/bash
# Register the ten data containers of the compose file with the metadata service.
# Usage: bash create_nodes.sh <admin token>
set -euo pipefail
if [ $# -ne 1 ]; then echo "usage: $0 <admin token>" >&2; exit 2; fi
COMPOSE=(docker compose -f "${DYNOSTORE_COMPOSE_FILE:-docker-compose.dev.yml}")
if [ -n "${DYNOSTORE_PROJECT:-}" ]; then COMPOSE+=(-p "$DYNOSTORE_PROJECT"); fi
for n in $(seq 1 10); do
  for attempt in 1 2 3 4 5; do
    if "${COMPOSE[@]}" exec -T "datacontainer$n" python3 regist_on_metadata.py "$1" "10.88.0.$((18 + n))"; then
      continue 2
    fi
    sleep 3
  done
  echo "Could not register datacontainer$n" >&2
  exit 1
done
