#!/usr/bin/env bash
# =============================================================================
# deploy_vps.sh — Bootstrap the contracts_app on a fresh Ubuntu VPS
#
# Usage (run as root on the VPS):
#   bash deploy_vps.sh
#
# What it does:
#   1. Installs Apache, PHP 8.3 + extensions, MySQL, Composer
#   2. Clones the GitHub repo
#   3. Runs composer install
#   4. Creates the storage directory layout and symlink
#   5. Configures Apache vhost with mod_rewrite
#   6. Creates the MySQL database and user
#   7. Imports the database dump (if present)
#   8. Writes a .env stub you must complete
# =============================================================================

set -euo pipefail

# ── colour helpers ────────────────────────────────────────────────────────────
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; NC='\033[0m'
info()    { echo -e "${GREEN}[INFO]${NC}  $*"; }
warn()    { echo -e "${YELLOW}[WARN]${NC}  $*"; }
error()   { echo -e "${RED}[ERR]${NC}   $*"; exit 1; }

# ── configuration (edit these before running) ─────────────────────────────────
APP_DIR="/var/www/contracts_app"
GITHUB_REPO="https://github.com/boredlawdude/jack.git"
APACHE_SERVERNAME="srv1476112.hstgr.cloud"   # change to your real domain once set up
PHP_VER="8.3"

DB_NAME="contract_manager"
DB_USER="contract_user"
# You will be prompted for the DB password; or set it here:
# DB_PASS="changeme"

# ── guard: must be root ───────────────────────────────────────────────────────
[[ "$EUID" -ne 0 ]] && error "Run this script as root (sudo bash deploy_vps.sh)"

# ── 1. system packages ────────────────────────────────────────────────────────
info "Updating package lists …"
apt-get update -qq

info "Adding ondrej/php PPA for PHP ${PHP_VER} …"
apt-get install -y -qq software-properties-common
add-apt-repository -y ppa:ondrej/php
apt-get update -qq

info "Installing Apache, PHP ${PHP_VER}, MySQL, unzip …"
apt-get install -y -qq \
    apache2 \
    mysql-server \
    php${PHP_VER} \
    php${PHP_VER}-cli \
    php${PHP_VER}-common \
    php${PHP_VER}-mysql \
    php${PHP_VER}-pdo \
    php${PHP_VER}-mbstring \
    php${PHP_VER}-xml \
    php${PHP_VER}-zip \
    php${PHP_VER}-gd \
    php${PHP_VER}-curl \
    php${PHP_VER}-intl \
    php${PHP_VER}-bcmath \
    php${PHP_VER}-opcache \
    libapache2-mod-php${PHP_VER} \
    unzip \
    git \
    curl

info "Enabling Apache modules …"
a2enmod rewrite headers
a2dissite 000-default 2>/dev/null || true

# ── 2. Composer ───────────────────────────────────────────────────────────────
if ! command -v composer &>/dev/null; then
    info "Installing Composer …"
    EXPECTED_CHECKSUM="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"
    if [[ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]]; then
        rm -f composer-setup.php
        error "Composer installer checksum mismatch — aborting."
    fi
    php composer-setup.php --quiet --install-dir=/usr/local/bin --filename=composer
    rm -f composer-setup.php
else
    info "Composer already installed ($(composer --version --no-ansi 2>&1 | head -1))"
fi

# ── 3. Clone / update repo ────────────────────────────────────────────────────
if [[ -d "$APP_DIR/.git" ]]; then
    info "Repo already cloned — pulling latest …"
    git -C "$APP_DIR" pull --ff-only
else
    info "Cloning $GITHUB_REPO → $APP_DIR …"
    git clone "$GITHUB_REPO" "$APP_DIR"
fi

# ── 4. Composer install ───────────────────────────────────────────────────────
info "Running composer install …"
composer install --no-dev --optimize-autoloader --working-dir="$APP_DIR"

# ── 5. Storage directories + symlink ─────────────────────────────────────────
info "Creating storage directories …"
mkdir -p "$APP_DIR/storage/contracts"
mkdir -p "$APP_DIR/storage/generated_docs"
mkdir -p "$APP_DIR/storage/templates"
mkdir -p "$APP_DIR/storage/__tmp"
touch    "$APP_DIR/storage/.gitkeep"

# public/storage symlink (already in repo but recreate to be safe)
if [[ ! -L "$APP_DIR/public/storage" ]]; then
    ln -s ../storage "$APP_DIR/public/storage"
fi

# ── 6. File ownership & permissions ──────────────────────────────────────────
info "Setting ownership …"
chown -R www-data:www-data "$APP_DIR"
find "$APP_DIR"         -type d -exec chmod 755 {} \;
find "$APP_DIR"         -type f -exec chmod 644 {} \;
chmod -R 775 "$APP_DIR/storage"

# ── 7. Apache vhost ───────────────────────────────────────────────────────────
VHOST_FILE="/etc/apache2/sites-available/contracts_app.conf"
info "Writing Apache vhost → $VHOST_FILE …"
cat > "$VHOST_FILE" <<VHOST
<VirtualHost *:80>
    ServerName   ${APACHE_SERVERNAME}
    DocumentRoot ${APP_DIR}/public

    <Directory ${APP_DIR}/public>
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks
    </Directory>

    ErrorLog  \${APACHE_LOG_DIR}/contracts_error.log
    CustomLog \${APACHE_LOG_DIR}/contracts_access.log combined
</VirtualHost>
VHOST

a2ensite contracts_app
systemctl reload apache2
info "Apache configured and reloaded."

# ── 8. MySQL setup ────────────────────────────────────────────────────────────
info "Setting up MySQL database …"

# Prompt for DB password if not hardcoded above
if [[ -z "${DB_PASS:-}" ]]; then
    read -r -s -p "Enter a password for the MySQL user '${DB_USER}': " DB_PASS
    echo
fi

mysql --user=root <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL

info "Database '${DB_NAME}' and user '${DB_USER}' ready."

# ── 9. Import database dump (if uploaded) ────────────────────────────────────
DUMP_FILE="$APP_DIR/deploy_db_export.sql"
if [[ -f "$DUMP_FILE" ]]; then
    info "Importing database dump from $DUMP_FILE …"
    mysql --user=root "$DB_NAME" < "$DUMP_FILE"
    info "Database import complete."
    warn "Remove the dump file after verifying:  rm $DUMP_FILE"
else
    warn "No dump file found at $DUMP_FILE"
    warn "If you want to import your data, upload deploy_db_export.sql to $APP_DIR and re-run:"
    warn "  mysql -u root $DB_NAME < $DUMP_FILE"
fi

# ── 10. Write .env stub ──────────────────────────────────────────────────────
ENV_FILE="$APP_DIR/.env"
if [[ -f "$ENV_FILE" ]]; then
    warn ".env already exists — skipping (edit manually if needed)."
else
    info "Writing .env stub → $ENV_FILE …"
    cat > "$ENV_FILE" <<ENV
APP_NAME="Contracts"
APP_ENV=production
APP_URL=http://${APACHE_SERVERNAME}

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=${DB_NAME}
DB_USER=${DB_USER}
DB_PASS=${DB_PASS}

SESSION_NAME=contracts_app_sess

SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_SECURE=tls
SMTP_USERNAME=
SMTP_PASSWORD=
MAIL_FROM_EMAIL=
MAIL_FROM_NAME="${APP_NAME:-Contracts}"

ONLYOFFICE_JWT_SECRET=
OO_SECRET=
ENV
    chown www-data:www-data "$ENV_FILE"
    chmod 640 "$ENV_FILE"
    warn "Fill in SMTP credentials in $ENV_FILE before testing email."
fi

# ── done ──────────────────────────────────────────────────────────────────────
echo
echo -e "${GREEN}════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}  Deployment complete!${NC}"
echo -e "${GREEN}════════════════════════════════════════════════════════${NC}"
echo
echo "  App dir  : $APP_DIR"
echo "  URL      : http://${APACHE_SERVERNAME}/"
echo
echo "  Next steps:"
echo "  1. Edit .env and fill in SMTP_PASSWORD and any other secrets:"
echo "       nano $ENV_FILE"
echo "  2. Verify the site loads: curl -I http://${APACHE_SERVERNAME}/"
echo "  3. (Optional) Add SSL with Let's Encrypt:"
echo "       apt-get install -y certbot python3-certbot-apache"
echo "       certbot --apache -d ${APACHE_SERVERNAME}"
echo "  4. Remove the DB dump once data looks correct:"
echo "       rm $APP_DIR/deploy_db_export.sql"
echo
