#!/usr/bin/env bash
set -euo pipefail

cp -n env.example .env || true

composer install

php spark migrate --all || true

echo "Bootstrap complete."
echo "Run with:"
echo "CI_ENVIRONMENT=cloud php spark serve --host 127.0.0.1 --port 8888"