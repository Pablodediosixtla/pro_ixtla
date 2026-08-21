#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "[1/2] Validando PHP..."
find . -type f -name '*.php' -not -path './vendor/*' -print0 | xargs -0 -n1 php -l >/dev/null
echo "PHP OK"

echo "[2/2] Validando JavaScript..."
if command -v node >/dev/null 2>&1; then
  find js -type f -name '*.js' -print0 | xargs -0 -n1 node --check >/dev/null
  echo "JavaScript OK"
else
  echo "Node no disponible; se omite validación JS."
fi
