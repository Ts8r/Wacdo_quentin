#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

if ! docker network inspect admin_proxy >/dev/null 2>&1; then
  docker network create admin_proxy
fi

docker compose up -d --build

docker compose exec -T wacdo_php php bin/init_db.php

docker compose exec -T wacdo_php php bin/seed_db.php

echo "[OK] WACDO initialisé et seedé."
