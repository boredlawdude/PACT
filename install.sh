#!/usr/bin/env bash

set -euo pipefail

# ============================================================
# PACT - Project and Contract Tracking
# Installation Script
# (c) 2026 John Schifano, Attorney
# ============================================================

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SCHEMA_FILE="$APP_DIR/database/schema.sql"
SEED_FILE="$APP_DIR/database/seeds/reference_data.sql"
ENV_FILE="$APP_DIR/.env"

echo
echo "============================================================"
echo " PACT Installation"
echo "============================================================"
echo
echo "Application directory:"
echo "  $APP_DIR"
echo

# ------------------------------------------------------------
# 1. Basic prerequisite checks
# ------------------------------------------------------------

echo "[1/7] Checking prerequisites..."

for cmd in php mysql composer; do
    if ! command -v "$cmd" >/dev/null 2>&1; then
        echo "ERROR: Required command '$cmd' was not found."
        exit 1
    fi
done

echo "  PHP:      $(php -r 'echo PHP_VERSION;')"
echo "  MySQL:    $(mysql --version | head -1)"
echo "  Composer: $(composer --version 2>/dev/null | head -1)"
echo

# ------------------------------------------------------------
# 2. Verify required application files
# ------------------------------------------------------------

echo "[2/7] Checking PACT files..."

if [ ! -f "$SCHEMA_FILE" ]; then
    echo "ERROR: Missing database/schema.sql"
    exit 1
fi

if [ ! -f "$SEED_FILE" ]; then
    echo "ERROR: Missing database/seeds/reference_data.sql"
    exit 1
fi

if [ ! -f "$ENV_FILE" ]; then
    echo
    echo "ERROR: .env does not exist."
    echo
    echo "Create the server-specific .env file before running"
    echo "the installer."
    exit 1
fi

echo "  Required files found."
echo

# ------------------------------------------------------------
# 3. Install PHP dependencies
# ------------------------------------------------------------

echo "[3/7] Installing PHP dependencies..."

cd "$APP_DIR"

composer install \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

echo

# ------------------------------------------------------------
# 4. Create required writable directories
# ------------------------------------------------------------

echo "[4/7] Creating application directories..."

mkdir -p \
    "$APP_DIR/storage" \
    "$APP_DIR/storage/contracts" \
    "$APP_DIR/storage/generated" \
    "$APP_DIR/storage/uploads" \
    "$APP_DIR/storage/__tmp" \
    "$APP_DIR/public/uploads"

echo "  Directories ready."
echo

# ------------------------------------------------------------
# 5. Read database configuration from .env
# ------------------------------------------------------------

echo "[5/7] Reading database configuration..."

get_env_value() {
    local key="$1"
    local value

    value="$(grep -E "^${key}=" "$ENV_FILE" | tail -1 | cut -d '=' -f2-)"

    # Remove surrounding double quotes
    value="${value#\"}"
    value="${value%\"}"

    # Remove surrounding single quotes
    value="${value#\'}"
    value="${value%\'}"

    printf '%s' "$value"
}
DB_HOST="$(get_env_value DB_HOST)"
DB_PORT="$(get_env_value DB_PORT)"
DB_NAME="$(get_env_value DB_NAME)"
DB_USER="$(get_env_value DB_USER)"
DB_PASS="$(get_env_value DB_PASS)"
# Optional database override for testing/installations.
# Example: ./install.sh --database=pact_install_test
for arg in "$@"; do
    case "$arg" in
        --database=*)
            DB_NAME="${arg#*=}"
            ;;
        *)
            echo "ERROR: Unknown option: $arg"
            echo "Usage: $0 [--database=DATABASE_NAME]"
            exit 1
            ;;
    esac
done 
DB_HOST="${DB_HOST:-localhost}"
DB_PORT="${DB_PORT:-3306}"

if [ -z "$DB_NAME" ] || [ -z "$DB_USER" ]; then
    echo "ERROR: DB_NAME and DB_USER must be configured in .env."
    exit 1
fi

echo "  Database: $DB_NAME"
echo "  Host:     $DB_HOST:$DB_PORT"
echo "  User:     $DB_USER"
echo

# ------------------------------------------------------------
# 6. Test database connection
# ------------------------------------------------------------

echo "[6/7] Testing database connection..."

MYSQL_ARGS=(
    --host="$DB_HOST"
    --port="$DB_PORT"
    --user="$DB_USER"
)

if [ -n "$DB_PASS" ]; then
    export MYSQL_PWD="$DB_PASS"
fi

if ! mysql "${MYSQL_ARGS[@]}" "$DB_NAME" \
    -e "SELECT 1;" >/dev/null 2>&1; then

    unset MYSQL_PWD || true

    echo
    echo "ERROR: Could not connect to database '$DB_NAME'."
    echo
    echo "Verify the database exists and the credentials in .env"
    echo "are correct."
    exit 1
fi

echo "  Database connection successful."
echo

# ------------------------------------------------------------
# 7. Install database
# ------------------------------------------------------------

echo "[7/7] Preparing database..."
echo

TABLE_COUNT="$(
    mysql "${MYSQL_ARGS[@]}" "$DB_NAME" \
        -Nse "SELECT COUNT(*) FROM information_schema.tables
              WHERE table_schema = DATABASE();"
)"

if [ "$TABLE_COUNT" -gt 0 ]; then

    echo "WARNING: Database '$DB_NAME' already contains $TABLE_COUNT tables."
    echo
    echo "The installer will NOT overwrite an existing database."
    echo
    echo "Database installation skipped."

else

    echo "Importing schema..."
    mysql "${MYSQL_ARGS[@]}" "$DB_NAME" < "$SCHEMA_FILE"

    echo "Importing reference data..."
    mysql "${MYSQL_ARGS[@]}" "$DB_NAME" < "$SEED_FILE"

    echo
    echo "Database installation complete."

fi

unset MYSQL_PWD || true

echo
echo "============================================================"
echo " PACT installation completed successfully."
echo "============================================================"
echo
echo "Next steps:"
echo
echo "  1. Configure Apache DocumentRoot to:"
echo "       $APP_DIR/public"
echo
echo "  2. Ensure Apache can write to storage and public/uploads."
echo
echo "  3. Create the first PACT administrator account."
echo
echo "  4. Review organization settings in PACT."
echo
