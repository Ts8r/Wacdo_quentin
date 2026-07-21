#!/bin/sh
set -eu

BASE_URL="${1:-http://localhost}"
COOKIE_JAR="$(mktemp)"

cleanup() {
    rm -f "$COOKIE_JAR"
}
trap cleanup EXIT

check_status() {
    label="$1"
    expected="$2"
    method="$3"
    path="$4"
    body="${5:-}"

    if [ -n "$body" ]; then
        status="$(curl -sS -o /tmp/wacdo-smoke-response.json -w '%{http_code}' -b "$COOKIE_JAR" -c "$COOKIE_JAR" -X "$method" "$BASE_URL$path" -H 'Content-Type: application/json' -H 'Accept: application/json' -d "$body")"
    else
        status="$(curl -sS -o /tmp/wacdo-smoke-response.json -w '%{http_code}' -b "$COOKIE_JAR" -c "$COOKIE_JAR" -X "$method" "$BASE_URL$path" -H 'Accept: application/json')"
    fi

    if [ "$status" != "$expected" ]; then
        echo "KO $label: attendu $expected, recu $status"
        cat /tmp/wacdo-smoke-response.json
        exit 1
    fi

    echo "OK $label ($status)"
}

check_status "health" "200" "GET" "/api/health"
check_status "catalogue public" "200" "GET" "/api/catalogue"
check_status "back office html" "200" "GET" "/"
check_status "commandes protegees sans session" "401" "GET" "/api/commandes"

if [ -n "${ADMIN_EMAIL:-}" ] && [ -n "${ADMIN_PASSWORD:-}" ]; then
    login_body="$(printf '{"email":"%s","mot_de_passe":"%s"}' "$ADMIN_EMAIL" "$ADMIN_PASSWORD")"
    check_status "login admin" "200" "POST" "/api/auth/login" "$login_body"

    case "$BASE_URL" in
        http://*)
            echo "INFO session admin non testee en HTTP: le cookie de session est Secure et demande HTTPS."
            ;;
        *)
            check_status "session courante" "200" "GET" "/api/auth/me"
            check_status "commandes admin" "200" "GET" "/api/commandes?limit=5&offset=0"
            check_status "ingredients admin" "200" "GET" "/api/ingredients?limit=5&offset=0"
            check_status "utilisateurs admin" "200" "GET" "/api/utilisateurs?limit=5&offset=0"
            ;;
    esac
else
    echo "INFO routes admin non testees: definir ADMIN_EMAIL et ADMIN_PASSWORD pour verifier la session."
fi

echo "Smoke backend termine."
