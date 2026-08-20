#!/usr/bin/env bash
set -euo pipefail

SOURCE_DIR="$(cd "$(dirname "$0")" && pwd)"
TARGET_DIR="${1:-}"

if [[ -z "$TARGET_DIR" ]]; then
  echo "Uso: ./ACTUALIZAR_SIN_TOCAR_ENV.sh /ruta/a/pro_ixtla"
  exit 1
fi

mkdir -p "$TARGET_DIR"
BACKUP=""
if [[ -f "$TARGET_DIR/.env" ]]; then
  BACKUP="$(mktemp)"
  cp "$TARGET_DIR/.env" "$BACKUP"
fi

rsync -av --delete \
  --exclude='.git/' \
  --exclude='.env' \
  --exclude='uploads/presupuesto/*' \
  "$SOURCE_DIR/" "$TARGET_DIR/"

if [[ -n "$BACKUP" ]]; then
  cp "$BACKUP" "$TARGET_DIR/.env"
  rm -f "$BACKUP"
  # Conserva host/usuario/password y apunta al nuevo schema.
  if grep -q '^DB_NAME=' "$TARGET_DIR/.env"; then
    sed -i.bak 's/^DB_NAME=.*/DB_NAME=ixtla01_dep02/' "$TARGET_DIR/.env" && rm -f "$TARGET_DIR/.env.bak"
  else
    printf '\nDB_NAME=ixtla01_dep02\n' >> "$TARGET_DIR/.env"
  fi
  echo "Configuración .env conservada; DB_NAME actualizado a ixtla01_dep02."
else
  cp "$SOURCE_DIR/.env.example" "$TARGET_DIR/.env"
  if grep -q '^DB_NAME=' "$TARGET_DIR/.env"; then
    sed -i.bak 's/^DB_NAME=.*/DB_NAME=ixtla01_dep02/' "$TARGET_DIR/.env" && rm -f "$TARGET_DIR/.env.bak"
  fi
  echo "No existía .env en destino; se creó desde .env.example. Completa las credenciales localmente."
fi

echo "Proyecto actualizado en: $TARGET_DIR"
