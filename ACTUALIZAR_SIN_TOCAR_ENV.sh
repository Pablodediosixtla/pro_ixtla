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
  cp -p "$TARGET_DIR/.env" "$BACKUP"
fi

rsync -av --delete \
  --exclude='.git/' \
  --exclude='.env' \
  --exclude='uploads/presupuesto/*' \
  "$SOURCE_DIR/" "$TARGET_DIR/"

if [[ -n "$BACKUP" ]]; then
  cp -p "$BACKUP" "$TARGET_DIR/.env"
  rm -f "$BACKUP"
  echo "Configuración .env conservada sin cambios."
elif [[ -f "$SOURCE_DIR/.env" ]]; then
  cp -p "$SOURCE_DIR/.env" "$TARGET_DIR/.env"
  echo "Se copió el .env incluido en esta entrega sin modificarlo."
else
  cp "$SOURCE_DIR/.env.example" "$TARGET_DIR/.env"
  echo "No existía .env; se creó desde .env.example. Completa las credenciales localmente."
fi

echo "Proyecto actualizado en: $TARGET_DIR"
