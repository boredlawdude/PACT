#!/usr/bin/env bash
set -euo pipefail

# Applies the user login events migration on the VPS using DB credentials
# from the app .env file.
#
# Usage on VPS:
#   bash scripts/vps_apply_user_login_events_migration.sh
#
# Optional overrides:
#   APP_DIR=/var/www/contracts_app MIGRATION_FILE=user_login_events_migration.sql bash scripts/vps_apply_user_login_events_migration.sh

APP_DIR="${APP_DIR:-/var/www/contracts_app}"
MIGRATION_FILE="${MIGRATION_FILE:-user_login_events_migration.sql}"
ENV_FILE="$APP_DIR/.env"
SQL_FILE="$APP_DIR/$MIGRATION_FILE"

info() { printf '[INFO] %s\n' "$*"; }
err() { printf '[ERROR] %s\n' "$*" >&2; exit 1; }

[[ -f "$ENV_FILE" ]] || err "Missing env file: $ENV_FILE"
[[ -f "$SQL_FILE" ]] || err "Missing migration file: $SQL_FILE"
command -v mysql >/dev/null 2>&1 || err "mysql client is not installed"

read_env_value() {
  local key="$1"
  local line
  line=$(grep -E "^${key}=" "$ENV_FILE" | tail -n 1 || true)
  line="${line#*=}"
  line="${line%\"}"
  line="${line#\"}"
  printf '%s' "$line"
}

DB_HOST="$(read_env_value DB_HOST)"
DB_PORT="$(read_env_value DB_PORT)"
DB_NAME="$(read_env_value DB_NAME)"
DB_USER="$(read_env_value DB_USER)"
DB_PASS="$(read_env_value DB_PASS)"

[[ -n "$DB_HOST" ]] || err "DB_HOST is missing in $ENV_FILE"
[[ -n "$DB_PORT" ]] || DB_PORT="3306"
[[ -n "$DB_NAME" ]] || err "DB_NAME is missing in $ENV_FILE"
[[ -n "$DB_USER" ]] || err "DB_USER is missing in $ENV_FILE"

info "Applying migration: $SQL_FILE"
if [[ -n "$DB_PASS" ]]; then
  MYSQL_PWD="$DB_PASS" mysql --host="$DB_HOST" --port="$DB_PORT" --user="$DB_USER" "$DB_NAME" < "$SQL_FILE"
else
  mysql --host="$DB_HOST" --port="$DB_PORT" --user="$DB_USER" "$DB_NAME" < "$SQL_FILE"
fi

info "Migration applied successfully."
