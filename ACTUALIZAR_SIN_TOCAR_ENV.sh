#!/usr/bin/env bash
set -euo pipefail

SOURCE_DIR="$(cd "$(dirname "$0")" && pwd)"
TARGET_DIR="${1:-}"

if [[ -z "$TARGET_DIR" ]]; then
  echo "Uso: ./ACTUALIZAR_SIN_TOCAR_ENV.sh /ruta/a/tu/pro_ixtla"
  exit 1
fi

TARGET_DIR="$(cd "$TARGET_DIR" && pwd)"

if [[ ! -d "$TARGET_DIR/.git" ]]; then
  echo "ERROR: $TARGET_DIR no parece ser el clon Git de pro_ixtla (.git no encontrado)."
  exit 1
fi

ENV_BACKUP=""
if [[ -f "$TARGET_DIR/.env" ]]; then
  ENV_BACKUP="$(mktemp /tmp/pro_ixtla_env.XXXXXX)"
  cp "$TARGET_DIR/.env" "$ENV_BACKUP"
  echo "Respaldo temporal de .env creado."
fi

rsync -av \
  --exclude='.git' \
  --exclude='.env' \
  --exclude='ACTUALIZAR_SIN_TOCAR_ENV.sh' \
  --exclude='uploads/presupuesto/*' \
  "$SOURCE_DIR/" "$TARGET_DIR/"

if [[ -n "$ENV_BACKUP" ]]; then
  cp "$ENV_BACKUP" "$TARGET_DIR/.env"
  rm -f "$ENV_BACKUP"
  echo ".env restaurado sin cambios."
fi

echo "Actualización aplicada. Revisa: git status"
