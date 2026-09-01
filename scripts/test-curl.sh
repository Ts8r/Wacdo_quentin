#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

set -a
[ -f .env ] && . ./.env
set +a

BACK_HOST="${BACK_DOMAIN:-${BACK_URL:-localhost}}"
FRONT_HOST="${FRONT_URL:-${BACK_HOST:-localhost}}"

echo "[1/3] Vérification backend local via le conteneur PHP"
docker compose exec -T wacdo_php php -r "echo file_get_contents('http://127.0.0.1/api/health');"
printf '\n\n'
docker compose exec -T wacdo_php php -r "echo file_get_contents('http://127.0.0.1/api/categories');" | head -c 300
printf '\n\n'

echo "[2/3] Vérification HTTPS du frontend si le domaine est exposé"
if command -v curl >/dev/null 2>&1; then
  if curl -fsS "https://${FRONT_HOST}" >/tmp/wacdo_front_check.txt 2>/tmp/wacdo_front_check.err; then
    echo "HTTPS ok sur ${FRONT_HOST}"
    head -c 200 /tmp/wacdo_front_check.txt
    printf '\n'
  else
    echo "HTTPS non disponible sur ${FRONT_HOST}; vérification locale uniquement."
    cat /tmp/wacdo_front_check.err 2>/dev/null || true
  fi
else
  echo "curl non installé sur l'hôte; skip HTTPS domain check."
fi

echo "[3/3] Vérification HTTPS du backend si le domaine est exposé"
if command -v curl >/dev/null 2>&1; then
  if curl -fsS "https://${BACK_HOST}/api/health" >/tmp/wacdo_back_check.txt 2>/tmp/wacdo_back_check.err; then
    echo "HTTPS ok sur ${BACK_HOST}"
    cat /tmp/wacdo_back_check.txt
    printf '\n'
  else
    echo "HTTPS non disponible sur ${BACK_HOST}; le domaine n'est pas encore routé sur cette machine."
    cat /tmp/wacdo_back_check.err 2>/dev/null || true
  fi
else
  echo "curl non installé sur l'hôte; skip HTTPS backend check."
fi
