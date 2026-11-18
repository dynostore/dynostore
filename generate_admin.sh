#!/bin/bash
# Create an administrator and print its token on standard output.
set -euo pipefail
COMPOSE=(docker compose -f "${DYNOSTORE_COMPOSE_FILE:-docker-compose.dev.yml}")
if [ -n "${DYNOSTORE_PROJECT:-}" ]; then COMPOSE+=(-p "$DYNOSTORE_PROJECT"); fi
"${COMPOSE[@]}" exec -T db_auth python3 configure/create_admin.py
