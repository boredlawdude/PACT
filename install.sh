#!/usr/bin/env bash

set -euo pipefail

# ============================================================
# PACT - Project and Contract Tracking
# Installation Script
# ============================================================

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SCHEMA_FILE="$APP_DIR/database/schema.sql"
SEED_FILE="$APP_DIR/database/seeds/reference_data.sql"
ENV_FILE="$APP_DIR/.env"

DB_HOST="localhost"
DB_PORT="3306"
DB_NAME_OVERRIDE=""
SERVER_NAME_OVERRIDE=""
ORG_NAME_OVERRIDE=""

MYSQL_CNF=""

# ============================================================
# Helpers
# ============================================================

die() {
    echo
    echo "ERROR: $1"
    echo
    exit 1
}

prompt_default() {
    local prompt="$1"
    local default="$2"
    local value=""

    read -r -p "$prompt [$default]: " value
    printf '%s' "${value:-$default}"
}

get_env_value() {
    local key="$1"
    local value=""

    if [ -f "$ENV_FILE" ]; then
        value="$(
            grep -E "^${key}=" "$ENV_FILE" 2>/dev/null \
                | tail -1 \
                | cut -d '=' -f2- || true
        )"

        value="${value#\"}"
        value="${value%\"}"
        value="${value#\'}"
        value="${value%\'}"
    fi

    printf '%s' "$value"
}

sql_escape() {
    local value="$1"

    value="${value//\\/\\\\}"
    value="${value//\'/\'\'}"

    printf '%s' "$value"
}

env_escape() {
    local value="$1"

    value="${value//\\/\\\\}"
    value="${value//\"/\\\"}"

    printf '%s' "$value"
}

cleanup() {
    if [ -n "${MYSQL_CNF:-}" ] && [ -f "$MYSQL_CNF" ]; then
        rm -f "$MYSQL_CNF"
    fi

    unset DB_PASS 2>/dev/null || true
    unset ADMIN_PASS 2>/dev/null || true
    unset ADMIN_PASS2 2>/dev/null || true
    unset ADMIN_HASH 2>/dev/null || true
}

trap cleanup EXIT

# ============================================================
# Command-line options
# ============================================================

for arg in "$@"; do
    case "$arg" in
        --database=*)
            DB_NAME_OVERRIDE="${arg#*=}"
            ;;
        --server-name=*)
            SERVER_NAME_OVERRIDE="${arg#*=}"
            ;;
        --organization=*)
            ORG_NAME_OVERRIDE="${arg#*=}"
            ;;
        --help|-h)
            echo "Usage:"
            echo "  ./install.sh"
            echo "  ./install.sh --database=NAME"
            echo "               --server-name=HOSTNAME"
            echo "               --organization=\"Organization Name\""
            exit 0
            ;;
        *)
            die "Unknown option: $arg"
            ;;
    esac
done

# ============================================================
# Header
# ============================================================

echo
echo "============================================================"
echo " PACT - Project and Contract Tracking"
echo " Installation"
echo "============================================================"
echo
echo "Application directory:"
echo "  $APP_DIR"
echo

# ============================================================
# 1. Prerequisites
# ============================================================

echo "[1/10] Checking prerequisites..."

for cmd in php mysql composer apache2ctl sudo; do
    if ! command -v "$cmd" >/dev/null 2>&1; then
        die "Required command '$cmd' was not found."
    fi
done

if [ ! -f "$SCHEMA_FILE" ]; then
    die "Missing database/schema.sql"
fi

if [ ! -f "$SEED_FILE" ]; then
    die "Missing database/seeds/reference_data.sql"
fi

echo "  PHP:      $(php -r 'echo PHP_VERSION;')"
echo "  MySQL:    $(mysql --version | head -1)"
echo "  Composer: $(composer --version 2>/dev/null | head -1)"
echo "  Apache:   $(apache2ctl -v 2>/dev/null | head -1)"
echo

echo "Checking sudo access..."
sudo -v
echo "  sudo access OK."
echo

# ============================================================
# 2. Installation information
# ============================================================

echo "[2/10] Installation settings..."
echo

EXISTING_DB_NAME="$(get_env_value DB_NAME)"
EXISTING_DB_USER="$(get_env_value DB_USER)"
EXISTING_APP_URL="$(get_env_value APP_URL)"

DEFAULT_DB_NAME="${EXISTING_DB_NAME:-contract_manager}"
DEFAULT_DB_USER="${EXISTING_DB_USER:-contract_user}"
DEFAULT_SERVER_NAME="pact.local"
DEFAULT_ORG_NAME="Town of Holly Springs"

if [ -n "$DB_NAME_OVERRIDE" ]; then
    DB_NAME="$DB_NAME_OVERRIDE"
else
    DB_NAME="$(prompt_default "Database name" "$DEFAULT_DB_NAME")"
fi

DB_USER="$(prompt_default "Database user" "$DEFAULT_DB_USER")"

echo
read -r -s -p "Database password: " DB_PASS
echo

if [ -z "$DB_PASS" ]; then
    die "Database password cannot be blank."
fi

if [ -n "$SERVER_NAME_OVERRIDE" ]; then
    SERVER_NAME="$SERVER_NAME_OVERRIDE"
else
    SERVER_NAME="$(prompt_default "PACT hostname" "$DEFAULT_SERVER_NAME")"
fi

if [ -n "$ORG_NAME_OVERRIDE" ]; then
    ORG_NAME="$ORG_NAME_OVERRIDE"
else
    ORG_NAME="$(prompt_default "Organization name" "$DEFAULT_ORG_NAME")"
fi

# Database identifiers are deliberately restricted.
if [[ ! "$DB_NAME" =~ ^[A-Za-z0-9_]+$ ]]; then
    die "Database name may contain only letters, numbers and underscores."
fi

if [[ ! "$DB_USER" =~ ^[A-Za-z0-9_]+$ ]]; then
    die "Database user may contain only letters, numbers and underscores."
fi

# Prevent Apache config injection.
if [[ ! "$SERVER_NAME" =~ ^[A-Za-z0-9.-]+$ ]]; then
    die "Hostname may contain only letters, numbers, periods and hyphens."
fi

if [ -z "$ORG_NAME" ]; then
    die "Organization name cannot be blank."
fi

echo
echo "Configuration:"
echo "  Database:      $DB_NAME"
echo "  Database user: $DB_USER"
echo "  Database host: $DB_HOST:$DB_PORT"
echo "  Hostname:      $SERVER_NAME"
echo "  Organization:  $ORG_NAME"
echo

read -r -p "Continue with installation? [y/N]: " CONFIRM

case "$CONFIRM" in
    y|Y|yes|YES)
        ;;
    *)
        echo
        echo "Installation cancelled."
        exit 0
        ;;
esac

echo

# ============================================================
# 3. PHP dependencies
# ============================================================

echo "[3/10] Installing PHP dependencies..."

cd "$APP_DIR"

composer install \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

echo

# ============================================================
# 4. Writable directories
# ============================================================

echo "[4/10] Creating application directories..."

mkdir -p \
    "$APP_DIR/storage" \
    "$APP_DIR/storage/contracts" \
    "$APP_DIR/storage/generated" \
    "$APP_DIR/storage/uploads" \
    "$APP_DIR/storage/__tmp" \
    "$APP_DIR/public/uploads"

sudo chown -R "$(id -un)":www-data \
    "$APP_DIR/storage" \
    "$APP_DIR/public/uploads"

sudo find \
    "$APP_DIR/storage" \
    "$APP_DIR/public/uploads" \
    -type d -exec chmod 775 {} \;

sudo find \
    "$APP_DIR/storage" \
    "$APP_DIR/public/uploads" \
    -type f -exec chmod 664 {} \;

echo "  Writable directories ready."
echo

# ============================================================
# 5. MySQL database and PACT database user
# ============================================================

echo "[5/10] Preparing MySQL..."

DB_USER_SQL="$(sql_escape "$DB_USER")"
DB_PASS_SQL="$(sql_escape "$DB_PASS")"

# Create the database first.
sudo mysql <<SQL
CREATE DATABASE IF NOT EXISTS \`$DB_NAME\`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
SQL

# Determine whether the requested MySQL account already exists.
DB_USER_EXISTS="$(
    sudo mysql -Nse "
        SELECT COUNT(*)
        FROM mysql.user
        WHERE User = '$DB_USER_SQL'
          AND Host = 'localhost';
    "
)"

if [ "$DB_USER_EXISTS" -eq 0 ]; then

    echo "  Creating MySQL user '$DB_USER'..."

    sudo mysql <<SQL
CREATE USER '$DB_USER_SQL'@'localhost'
    IDENTIFIED BY '$DB_PASS_SQL';

GRANT ALL PRIVILEGES
    ON \`$DB_NAME\`.*
    TO '$DB_USER_SQL'@'localhost';

FLUSH PRIVILEGES;
SQL

else

    echo "  MySQL user '$DB_USER' already exists."
    echo "  Existing password will NOT be changed."

    # Granting database access does not alter the existing password.
    sudo mysql <<SQL
GRANT ALL PRIVILEGES
    ON \`$DB_NAME\`.*
    TO '$DB_USER_SQL'@'localhost';

FLUSH PRIVILEGES;
SQL

fi

# Use a temporary MySQL option file so the database password
# is not placed on the command line.

MYSQL_CNF="$(mktemp)"
chmod 600 "$MYSQL_CNF"

DB_PASS_CNF="${DB_PASS//\\/\\\\}"
DB_PASS_CNF="${DB_PASS_CNF//\"/\\\"}"

cat > "$MYSQL_CNF" <<EOF
[client]
host=$DB_HOST
port=$DB_PORT
user=$DB_USER
password="$DB_PASS_CNF"
EOF

MYSQL_ARGS=(
    --defaults-extra-file="$MYSQL_CNF"
)

if ! mysql "${MYSQL_ARGS[@]}" "$DB_NAME" \
    -e "SELECT 1;" >/dev/null 2>&1; then

    die "PACT database user could not connect to '$DB_NAME'."
fi

echo "  Database connection successful."
echo

# ============================================================
# 6. Schema and reference data
# ============================================================

echo "[6/10] Installing PACT database..."

TABLE_COUNT="$(
    mysql "${MYSQL_ARGS[@]}" "$DB_NAME" -Nse "
        SELECT COUNT(*)
        FROM information_schema.tables
        WHERE table_schema = DATABASE();
    "
)"

if [ "$TABLE_COUNT" -gt 0 ]; then

    echo
    echo "WARNING: Database '$DB_NAME' already contains $TABLE_COUNT tables."
    echo "For safety, schema and seed import will NOT run."
    echo

else

    echo "  Importing schema..."
    mysql "${MYSQL_ARGS[@]}" "$DB_NAME" < "$SCHEMA_FILE"

    echo "  Importing reference data..."
    mysql "${MYSQL_ARGS[@]}" "$DB_NAME" < "$SEED_FILE"

    echo "  Database installation complete."
    echo

fi

# Verify the tables needed for setup actually exist.

for required_table in \
    people \
    roles \
    person_roles \
    organization_settings \
    companies
do
    EXISTS="$(
        mysql "${MYSQL_ARGS[@]}" "$DB_NAME" -Nse "
            SELECT COUNT(*)
            FROM information_schema.tables
            WHERE table_schema = DATABASE()
              AND table_name = '$required_table';
        "
    )"

    if [ "$EXISTS" -ne 1 ]; then
        die "Required table '$required_table' does not exist."
    fi
done

# ============================================================
# 7. Generate .env
# ============================================================

echo "[7/10] Configuring .env..."

WRITE_ENV="yes"

if [ -f "$ENV_FILE" ]; then
    echo
    echo "An existing .env file was found:"
    echo "  $ENV_FILE"
    echo

    read -r -p "Replace it with the new installation settings? [y/N]: " REPLACE_ENV

    case "$REPLACE_ENV" in
        y|Y|yes|YES)
            BACKUP="$ENV_FILE.backup.$(date +%Y%m%d_%H%M%S)"
            cp "$ENV_FILE" "$BACKUP"
            chmod 600 "$BACKUP"
            echo "  Existing .env backed up to:"
            echo "  $BACKUP"
            ;;
        *)
            WRITE_ENV="no"
            echo "  Existing .env will be left unchanged."
            ;;
    esac
fi

if [ "$WRITE_ENV" = "yes" ]; then

    DB_PASS_ENV="$(env_escape "$DB_PASS")"
    ORG_NAME_ENV="$(env_escape "$ORG_NAME")"

    cat > "$ENV_FILE" <<EOF
APP_NAME="PACT"
APP_ENV=production
APP_URL="http://$SERVER_NAME"

DB_HOST="$DB_HOST"
DB_PORT="$DB_PORT"
DB_NAME="$DB_NAME"
DB_USER="$DB_USER"
DB_PASS="$DB_PASS_ENV"

SESSION_NAME=PACT_SESSION

SMTP_HOST=
SMTP_PORT=587
SMTP_SECURE=tls
SMTP_USERNAME=
SMTP_PASSWORD=
MAIL_FROM_EMAIL=
MAIL_FROM_NAME="$ORG_NAME_ENV"

DOCUSIGN_ENV=
DOCUSIGN_CLIENT_ID=
DOCUSIGN_CLIENT_SECRET=
DOCUSIGN_REDIRECT_URI=
DOCUSIGN_WEBHOOK_HMAC_KEY=

ONLYOFFICE_DOCUMENT_SERVER_URL=
ONLYOFFICE_APP_BASE_URL=
ONLYOFFICE_JWT_SECRET=
OO_SECRET=

NEXTCLOUD_BASE_URL=
NEXTCLOUD_WEBDAV_ROOT=

PROJECT_MANAGER_APP_URL=
EOF

    chmod 600 "$ENV_FILE"

    echo "  .env created."

fi

echo

# ============================================================
# 8. Organization and initial SUPERUSER
# ============================================================

echo "[8/10] Initial administrator..."
echo

read -r -p "Administrator first name: " ADMIN_FIRST
read -r -p "Administrator last name: " ADMIN_LAST
read -r -p "Administrator email: " ADMIN_EMAIL

if [ -z "$ADMIN_FIRST" ]; then
    die "Administrator first name is required."
fi

if [ -z "$ADMIN_LAST" ]; then
    die "Administrator last name is required."
fi

if [ -z "$ADMIN_EMAIL" ]; then
    die "Administrator email is required."
fi

if [[ "$ADMIN_EMAIL" != *@*.* ]]; then
    die "Administrator email does not appear valid."
fi

while true; do

    echo
    read -r -s -p "Administrator password: " ADMIN_PASS
    echo

    read -r -s -p "Confirm administrator password: " ADMIN_PASS2
    echo

    if [ "$ADMIN_PASS" != "$ADMIN_PASS2" ]; then
        echo "Passwords do not match. Try again."
        continue
    fi

    if [ "${#ADMIN_PASS}" -lt 12 ]; then
        echo "Password must contain at least 12 characters."
        continue
    fi

    break

done

# Generate the password hash using the same PHP password_hash()
# mechanism used by PACT.

ADMIN_HASH="$(
    printf '%s' "$ADMIN_PASS" |
        php -r '
            $password = stream_get_contents(STDIN);
            echo password_hash($password, PASSWORD_DEFAULT);
        '
)"

if [ -z "$ADMIN_HASH" ]; then
    die "Could not generate administrator password hash."
fi

ADMIN_FIRST_SQL="$(sql_escape "$ADMIN_FIRST")"
ADMIN_LAST_SQL="$(sql_escape "$ADMIN_LAST")"
ADMIN_EMAIL_SQL="$(sql_escape "$ADMIN_EMAIL")"
ADMIN_HASH_SQL="$(sql_escape "$ADMIN_HASH")"
ORG_NAME_SQL="$(sql_escape "$ORG_NAME")"

EXISTING_ADMIN="$(
    mysql "${MYSQL_ARGS[@]}" "$DB_NAME" -Nse "
        SELECT person_id
        FROM people
        WHERE email = '$ADMIN_EMAIL_SQL'
        LIMIT 1;
    "
)"

if [ -n "$EXISTING_ADMIN" ]; then

    if [[ ! "$EXISTING_ADMIN" =~ ^[0-9]+$ ]]; then
        die "Unexpected administrator person_id."
    fi

    ADMIN_ID="$EXISTING_ADMIN"

    mysql "${MYSQL_ARGS[@]}" "$DB_NAME" <<SQL
UPDATE people
SET
    first_name = '$ADMIN_FIRST_SQL',
    last_name = '$ADMIN_LAST_SQL',
    password_hash = '$ADMIN_HASH_SQL',
    can_login = 1,
    is_active = 1
WHERE person_id = $ADMIN_ID;
SQL

    echo "  Existing person enabled for login."

else

    mysql "${MYSQL_ARGS[@]}" "$DB_NAME" <<SQL
INSERT INTO people
(
    first_name,
    last_name,
    email,
    company_id,
    is_active,
    is_town_employee,
    password_hash,
    can_login
)
VALUES
(
    '$ADMIN_FIRST_SQL',
    '$ADMIN_LAST_SQL',
    '$ADMIN_EMAIL_SQL',
    3,
    1,
    1,
    '$ADMIN_HASH_SQL',
    1
);
SQL

    ADMIN_ID="$(
        mysql "${MYSQL_ARGS[@]}" "$DB_NAME" -Nse "
            SELECT person_id
            FROM people
            WHERE email = '$ADMIN_EMAIL_SQL'
            LIMIT 1;
        "
    )"

    if [[ ! "$ADMIN_ID" =~ ^[0-9]+$ ]]; then
        die "Could not determine administrator person_id."
    fi

    echo "  Administrator person created."

fi

SUPERUSER_ROLE_ID="$(
    mysql "${MYSQL_ARGS[@]}" "$DB_NAME" -Nse "
        SELECT role_id
        FROM roles
        WHERE role_key = 'SUPERUSER'
          AND is_active = 1
        LIMIT 1;
    "
)"

if [[ ! "$SUPERUSER_ROLE_ID" =~ ^[0-9]+$ ]]; then
    die "Active SUPERUSER role was not found."
fi

mysql "${MYSQL_ARGS[@]}" "$DB_NAME" <<SQL
INSERT IGNORE INTO person_roles
    (person_id, role_id)
VALUES
    ($ADMIN_ID, $SUPERUSER_ROLE_ID);
SQL

# Replace the seed organization's name with the organization
# supplied during installation.

mysql "${MYSQL_ARGS[@]}" "$DB_NAME" <<SQL
UPDATE organization_settings
SET org_name = '$ORG_NAME_SQL'
WHERE id = 1;

UPDATE companies
SET name = '$ORG_NAME_SQL'
WHERE company_id = 3;
SQL

unset ADMIN_PASS
unset ADMIN_PASS2
unset ADMIN_HASH

echo "  SUPERUSER role assigned."
echo "  Organization name configured."
echo

# ============================================================
# 9. Apache
# ============================================================

echo "[9/10] Configuring Apache..."

SAFE_SITE_NAME="$(printf '%s' "$SERVER_NAME" | tr '.-' '__')"
APACHE_SITE="/etc/apache2/sites-available/${SAFE_SITE_NAME}.conf"

if [ -f "$APACHE_SITE" ]; then
    APACHE_BACKUP="$APACHE_SITE.backup.$(date +%Y%m%d_%H%M%S)"
    sudo cp "$APACHE_SITE" "$APACHE_BACKUP"

    echo "  Existing Apache site backed up to:"
    echo "  $APACHE_BACKUP"
fi

sudo tee "$APACHE_SITE" >/dev/null <<EOF
<VirtualHost *:80>
    ServerName $SERVER_NAME

    DocumentRoot $APP_DIR/public

    <Directory $APP_DIR/public>
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/pact-error.log
    CustomLog \${APACHE_LOG_DIR}/pact-access.log combined
</VirtualHost>
EOF

sudo a2enmod rewrite >/dev/null
sudo a2ensite "$(basename "$APACHE_SITE")" >/dev/null

echo "  Testing Apache configuration..."

if ! sudo apache2ctl configtest; then
    die "Apache configuration test failed. Apache was not reloaded."
fi

sudo systemctl reload apache2

echo "  Apache configured and reloaded."
echo

# ============================================================
# 10. Verification
# ============================================================

echo "[10/10] Verifying installation..."

ROLE_COUNT="$(
    mysql "${MYSQL_ARGS[@]}" "$DB_NAME" \
        -Nse "SELECT COUNT(*) FROM roles;"
)"

CONTRACT_TYPE_COUNT="$(
    mysql "${MYSQL_ARGS[@]}" "$DB_NAME" \
        -Nse "SELECT COUNT(*) FROM contract_types;"
)"

DEPARTMENT_COUNT="$(
    mysql "${MYSQL_ARGS[@]}" "$DB_NAME" \
        -Nse "SELECT COUNT(*) FROM departments;"
)"

ADMIN_CHECK="$(
    mysql "${MYSQL_ARGS[@]}" "$DB_NAME" -Nse "
        SELECT COUNT(*)
        FROM person_roles pr
        JOIN roles r
          ON r.role_id = pr.role_id
        WHERE pr.person_id = $ADMIN_ID
          AND r.role_key = 'SUPERUSER';
    "
)"

if [ "$ROLE_COUNT" -lt 1 ]; then
    die "Role verification failed."
fi

if [ "$CONTRACT_TYPE_COUNT" -lt 1 ]; then
    die "Contract type verification failed."
fi

if [ "$DEPARTMENT_COUNT" -lt 1 ]; then
    die "Department verification failed."
fi

if [ "$ADMIN_CHECK" -ne 1 ]; then
    die "SUPERUSER verification failed."
fi

echo "  Database:        OK"
echo "  Reference data:  OK"
echo "  SUPERUSER:       OK"
echo "  Apache:          OK"
echo

echo "============================================================"
echo " PACT installation completed successfully"
echo "============================================================"
echo
echo "PACT URL:"
echo "  http://$SERVER_NAME"
echo
echo "Administrator:"
echo "  $ADMIN_EMAIL"
echo
echo "Application directory:"
echo "  $APP_DIR"
echo
echo "Database:"
echo "  $DB_NAME"
echo
echo "Optional integrations may now be configured in .env:"
echo "  SMTP"
echo "  DocuSign"
echo "  OnlyOffice"
echo "  Nextcloud"
echo "  Project Manager"
echo
echo "IMPORTANT:"
echo "If '$SERVER_NAME' is an internal hostname, DNS or the client"
echo "hosts file must resolve it to this server."
echo
echo "HTTPS/TLS is not configured by this installer."
echo
