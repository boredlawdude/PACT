#!/usr/bin/env bash

set -euo pipefail

# ============================================================
# PACT Server Provisioner
# Base Server - Version 1
#
# Prepares an Ubuntu server for PACT and related applications.
#
# Installs:
#   - Apache
#   - PHP and required extensions
#   - MySQL
#   - Composer
#   - Git and utilities
#   - LibreOffice headless/document utilities
#   - Docker
#
# Does NOT install:
#   - PACT itself
#   - Nextcloud
#   - ONLYOFFICE Docs
# ============================================================

export DEBIAN_FRONTEND=noninteractive

die() {
    echo
    echo "ERROR: $1"
    echo
    exit 1
}

section() {
    echo
    echo "============================================================"
    echo " $1"
    echo "============================================================"
    echo
}

command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# ------------------------------------------------------------
# 1. Preflight
# ------------------------------------------------------------

section "PACT Server Provisioner"

if [ "$(id -u)" -eq 0 ]; then
    die "Run this script as your normal sudo-capable user, not as root."
fi

if ! command_exists sudo; then
    die "sudo is required."
fi

if [ ! -f /etc/os-release ]; then
    die "Unable to determine operating system."
fi

# shellcheck disable=SC1091
source /etc/os-release

if [ "${ID:-}" != "ubuntu" ]; then
    die "This provisioner currently supports Ubuntu only."
fi

echo "Operating system: ${PRETTY_NAME:-Ubuntu}"
echo "Hostname:         $(hostname)"
echo "Provisioning user: $(id -un)"
echo

echo "Checking sudo access..."
sudo -v
echo "sudo access OK."

# ------------------------------------------------------------
# 2. System resources
# ------------------------------------------------------------

section "Checking Server Resources"

TOTAL_RAM_MB="$(free -m | awk '/^Mem:/ {print $2}')"
AVAILABLE_DISK_GB="$(df -BG / | awk 'NR==2 {gsub("G","",$4); print $4}')"
SWAP_MB="$(free -m | awk '/^Swap:/ {print $2}')"

echo "RAM:            ${TOTAL_RAM_MB} MB"
echo "Root free disk: ${AVAILABLE_DISK_GB} GB"
echo "Swap:           ${SWAP_MB} MB"

if [ "$TOTAL_RAM_MB" -lt 4096 ]; then
    echo
    echo "WARNING: Less than 4 GB RAM detected."
    echo "PACT may run, but Nextcloud + ONLYOFFICE will require more resources."
fi

if [ "$AVAILABLE_DISK_GB" -lt 20 ]; then
    echo
    echo "WARNING: Less than 20 GB free on the root filesystem."
fi

# ------------------------------------------------------------
# 3. Update package information
# ------------------------------------------------------------

section "Updating Ubuntu Packages"

sudo apt-get update

# Do not automatically perform a full distribution upgrade.
# That is intentionally left to the administrator.

# ------------------------------------------------------------
# 4. Base utilities
# ------------------------------------------------------------

section "Installing Base Utilities"

sudo apt-get install -y \
    ca-certificates \
    curl \
    wget \
    unzip \
    zip \
    bzip2 \
    git \
    rsync \
    gnupg \
    lsb-release \
    software-properties-common \
    openssl \
    cron

# ------------------------------------------------------------
# 5. Apache
# ------------------------------------------------------------

section "Installing Apache"

sudo apt-get install -y apache2

sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod expires
sudo a2enmod proxy
sudo a2enmod proxy_http
sudo a2enmod ssl

sudo systemctl enable apache2
sudo systemctl start apache2

if ! sudo apache2ctl configtest; then
    die "Apache configuration test failed."
fi

echo "Apache is running."

# ------------------------------------------------------------
# 6. PHP
# ------------------------------------------------------------

section "Installing PHP"

sudo apt-get install -y \
    php \
    libapache2-mod-php \
    php-cli \
    php-common \
    php-mysql \
    php-curl \
    php-gd \
    php-mbstring \
    php-xml \
    php-zip \
    php-intl \
    php-bcmath \
    php-soap \
    php-imagick \
    php-apcu

PHP_VERSION="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')"

echo "PHP $PHP_VERSION installed."

REQUIRED_EXTENSIONS=(
    curl
    gd
    mbstring
    mysqli
    pdo_mysql
    xml
    zip
    intl
    bcmath
)

MISSING_EXTENSIONS=()

for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if ! php -m | grep -qi "^${ext}$"; then
        MISSING_EXTENSIONS+=("$ext")
    fi
done

if [ "${#MISSING_EXTENSIONS[@]}" -gt 0 ]; then
    echo
    echo "WARNING: These PHP extensions were not detected:"
    printf '  %s\n' "${MISSING_EXTENSIONS[@]}"
else
    echo "Required PHP extensions detected."
fi

sudo systemctl restart apache2

# ------------------------------------------------------------
# 7. MySQL
# ------------------------------------------------------------

section "Installing MySQL"

sudo apt-get install -y mysql-server

sudo systemctl enable mysql
sudo systemctl start mysql

if sudo mysql -e "SELECT VERSION();" >/dev/null 2>&1; then
    echo "MySQL administrative connection successful."
else
    die "Unable to connect to MySQL using local administrative authentication."
fi

# ------------------------------------------------------------
# 8. Composer
# ------------------------------------------------------------

section "Installing Composer"

if command_exists composer; then

    echo "Composer already installed:"
    composer --version

else

    EXPECTED_CHECKSUM="$(
        php -r "copy('https://composer.github.io/installer.sig', 'php://stdout');"
    )"

    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"

    ACTUAL_CHECKSUM="$(
        php -r "echo hash_file('sha384', 'composer-setup.php');"
    )"

    if [ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]; then
        rm -f composer-setup.php
        die "Composer installer checksum verification failed."
    fi

    php composer-setup.php --quiet

    sudo mv composer.phar /usr/local/bin/composer

    rm -f composer-setup.php

    echo "Composer installed:"
    composer --version

fi

# ------------------------------------------------------------
# 9. LibreOffice and document/PDF utilities
# ------------------------------------------------------------

section "Installing Document Utilities"

sudo apt-get install -y \
    libreoffice-core \
    libreoffice-writer \
    libreoffice-calc \
    libreoffice-impress \
    poppler-utils \
    ghostscript \
    imagemagick

if command_exists libreoffice; then
    echo "LibreOffice installed:"
    libreoffice --version
else
    echo "WARNING: LibreOffice executable was not detected."
fi

if command_exists pdftotext; then
    echo "Poppler PDF utilities installed."
fi

if command_exists gs; then
    echo "Ghostscript installed."
fi

# ------------------------------------------------------------
# 10. Docker
# ------------------------------------------------------------

section "Installing Docker"

if command_exists docker; then

    echo "Docker already installed."

else

    sudo apt-get install -y docker.io

fi

sudo systemctl enable docker
sudo systemctl start docker

# Allow the provisioning user to use Docker without sudo.
# Group membership takes effect on the next login.

if ! id -nG "$(id -un)" | grep -qw docker; then
    sudo usermod -aG docker "$(id -un)"
    DOCKER_GROUP_ADDED="yes"
else
    DOCKER_GROUP_ADDED="no"
fi

if sudo docker info >/dev/null 2>&1; then
    echo "Docker service is operational."
else
    die "Docker was installed but is not responding correctly."
fi

# ------------------------------------------------------------
# 11. Cron
# ------------------------------------------------------------

section "Checking Cron"

sudo systemctl enable cron
sudo systemctl start cron

echo "Cron is running."

# ------------------------------------------------------------
# 12. Optional Nextcloud installation
# ------------------------------------------------------------

section "Nextcloud"

read -r -p "Install Nextcloud on this server? [y/N]: " INSTALL_NEXTCLOUD
INSTALL_NEXTCLOUD="${INSTALL_NEXTCLOUD:-n}"

NEXTCLOUD_INSTALLED="no"

if [[ "$INSTALL_NEXTCLOUD" =~ ^[Yy]$ ]]; then

    NEXTCLOUD_DIR="/var/www/nextcloud"
    NEXTCLOUD_DATA="/var/nextcloud-data"
    NEXTCLOUD_DB="nextcloud"
    NEXTCLOUD_DB_USER="nextcloud"

    read -r -p "Nextcloud hostname [cloud-test.local]: " NEXTCLOUD_HOST
    NEXTCLOUD_HOST="${NEXTCLOUD_HOST:-cloud-test.local}"

    echo
    echo "Nextcloud configuration:"
    echo "  Hostname:       $NEXTCLOUD_HOST"
    echo "  Install path:   $NEXTCLOUD_DIR"
    echo "  Data path:      $NEXTCLOUD_DATA"
    echo "  Database:       $NEXTCLOUD_DB"
    echo "  Database user:  $NEXTCLOUD_DB_USER"
    echo

    if [ -f "$NEXTCLOUD_DIR/config/config.php" ]; then
        echo "An existing Nextcloud installation was detected at:"
        echo "  $NEXTCLOUD_DIR"
        echo
        echo "Skipping fresh Nextcloud installation."
        NEXTCLOUD_INSTALLED="existing"
    else

        section "Installing Nextcloud Dependencies"

        sudo apt-get install -y \
            php-gmp \
            php-redis \
            redis-server \
            ffmpeg

        sudo systemctl enable redis-server
        sudo systemctl start redis-server

        # Nextcloud recommends APCu for local caching. occ also needs
        # APCu enabled for PHP CLI operations.
        PHP_CLI_INI="/etc/php/${PHP_VERSION}/cli/conf.d/99-nextcloud-apcu.ini"

        echo "apc.enable_cli=1" | sudo tee "$PHP_CLI_INI" >/dev/null

        section "Downloading Nextcloud"

        TMP_NEXTCLOUD="$(mktemp -d)"

        curl -fsSL \
            https://download.nextcloud.com/server/releases/latest.tar.bz2 \
            -o "$TMP_NEXTCLOUD/nextcloud.tar.bz2"

        tar -xjf "$TMP_NEXTCLOUD/nextcloud.tar.bz2" \
            -C "$TMP_NEXTCLOUD"

        if [ ! -f "$TMP_NEXTCLOUD/nextcloud/occ" ]; then
            rm -rf "$TMP_NEXTCLOUD"
            die "Downloaded Nextcloud archive does not contain occ."
        fi

        if [ -e "$NEXTCLOUD_DIR" ]; then
            die "$NEXTCLOUD_DIR already exists but does not appear to be an installed Nextcloud instance."
        fi

        sudo mv "$TMP_NEXTCLOUD/nextcloud" "$NEXTCLOUD_DIR"
        rm -rf "$TMP_NEXTCLOUD"

        sudo mkdir -p "$NEXTCLOUD_DATA"

        sudo chown -R www-data:www-data "$NEXTCLOUD_DIR"
        sudo chown -R www-data:www-data "$NEXTCLOUD_DATA"

        section "Creating Nextcloud Database"

        NEXTCLOUD_DB_PASS="$(openssl rand -hex 24)"

        SQL_DB_PASS="${NEXTCLOUD_DB_PASS//\'/\'\'}"

        sudo mysql <<SQL
CREATE DATABASE IF NOT EXISTS \`${NEXTCLOUD_DB}\`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

CREATE USER IF NOT EXISTS '${NEXTCLOUD_DB_USER}'@'localhost'
    IDENTIFIED BY '${SQL_DB_PASS}';

GRANT ALL PRIVILEGES
    ON \`${NEXTCLOUD_DB}\`.*
    TO '${NEXTCLOUD_DB_USER}'@'localhost';

FLUSH PRIVILEGES;
SQL

        section "Creating Nextcloud Administrator"

        read -r -p "Nextcloud administrator username [admin]: " NEXTCLOUD_ADMIN
        NEXTCLOUD_ADMIN="${NEXTCLOUD_ADMIN:-admin}"

        while true; do
            read -r -s -p "Nextcloud administrator password: " NEXTCLOUD_ADMIN_PASS
            echo
            read -r -s -p "Confirm administrator password: " NEXTCLOUD_ADMIN_PASS_CONFIRM
            echo

            if [ -z "$NEXTCLOUD_ADMIN_PASS" ]; then
                echo "Password cannot be blank."
                continue
            fi

            if [ "$NEXTCLOUD_ADMIN_PASS" != "$NEXTCLOUD_ADMIN_PASS_CONFIRM" ]; then
                echo "Passwords do not match. Try again."
                continue
            fi

            break
        done

        section "Installing Nextcloud"

        # Passwords are supplied interactively to occ rather than placed
        # directly in the command line.
        printf '%s\n%s\n' \
            "$NEXTCLOUD_DB_PASS" \
            "$NEXTCLOUD_ADMIN_PASS" \
        | sudo -u www-data php \
            --define apc.enable_cli=1 \
            "$NEXTCLOUD_DIR/occ" \
            maintenance:install \
            --database mysql \
            --database-name "$NEXTCLOUD_DB" \
            --database-host localhost \
            --database-user "$NEXTCLOUD_DB_USER" \
            --data-dir "$NEXTCLOUD_DATA" \
            --admin-user "$NEXTCLOUD_ADMIN"

        unset NEXTCLOUD_ADMIN_PASS
        unset NEXTCLOUD_ADMIN_PASS_CONFIRM
        unset NEXTCLOUD_DB_PASS
        unset SQL_DB_PASS

        section "Configuring Nextcloud"

        sudo -u www-data php \
            --define apc.enable_cli=1 \
            "$NEXTCLOUD_DIR/occ" \
            config:system:set trusted_domains 1 \
            --value="$NEXTCLOUD_HOST"

        sudo -u www-data php \
            --define apc.enable_cli=1 \
            "$NEXTCLOUD_DIR/occ" \
            config:system:set memcache.local \
            --value='\OC\Memcache\APCu'

        sudo -u www-data php \
            --define apc.enable_cli=1 \
            "$NEXTCLOUD_DIR/occ" \
            config:system:set memcache.locking \
            --value='\OC\Memcache\Redis'

        sudo -u www-data php \
            --define apc.enable_cli=1 \
            "$NEXTCLOUD_DIR/occ" \
            config:system:set redis host \
            --value='127.0.0.1'

        sudo -u www-data php \
            --define apc.enable_cli=1 \
            "$NEXTCLOUD_DIR/occ" \
            config:system:set redis port \
            --type=integer \
            --value=6379

        section "Configuring Nextcloud Apache Site"

        SAFE_NC_SITE="$(printf '%s' "$NEXTCLOUD_HOST" | tr '.-' '__')"
        NEXTCLOUD_APACHE_SITE="/etc/apache2/sites-available/${SAFE_NC_SITE}.conf"

        sudo tee "$NEXTCLOUD_APACHE_SITE" >/dev/null <<APACHE
<VirtualHost *:80>
    ServerName ${NEXTCLOUD_HOST}
    DocumentRoot ${NEXTCLOUD_DIR}

    <Directory ${NEXTCLOUD_DIR}>
        Require all granted
        AllowOverride All
        Options FollowSymLinks MultiViews
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/${SAFE_NC_SITE}_error.log
    CustomLog \${APACHE_LOG_DIR}/${SAFE_NC_SITE}_access.log combined
</VirtualHost>
APACHE

        sudo a2enmod rewrite headers env dir mime setenvif
        sudo a2ensite "$(basename "$NEXTCLOUD_APACHE_SITE")" >/dev/null

        if ! sudo apache2ctl configtest; then
            die "Apache configuration test failed after adding Nextcloud."
        fi

        sudo systemctl reload apache2

        section "Configuring Nextcloud Cron"

        NEXTCLOUD_CRON="*/5 * * * * php -f ${NEXTCLOUD_DIR}/cron.php"

        (
            sudo crontab -u www-data -l 2>/dev/null \
                | grep -vF "${NEXTCLOUD_DIR}/cron.php" || true
            echo "$NEXTCLOUD_CRON"
        ) | sudo crontab -u www-data -

        sudo -u www-data php \
            --define apc.enable_cli=1 \
            "$NEXTCLOUD_DIR/occ" \
            background:cron

        section "Verifying Nextcloud"

        sudo -u www-data php \
            --define apc.enable_cli=1 \
            "$NEXTCLOUD_DIR/occ" status

        NEXTCLOUD_INSTALLED="yes"

        echo
        echo "Nextcloud installation completed."
        echo
        echo "Hostname: $NEXTCLOUD_HOST"
        echo "URL:      http://$NEXTCLOUD_HOST"
        echo

    fi

else
    echo "Skipping Nextcloud."
fi

# ------------------------------------------------------------
# 13. Optional ONLYOFFICE Docs installation
# ------------------------------------------------------------

section "ONLYOFFICE Docs"

ONLYOFFICE_INSTALLED="no"

if [ ! -f "/var/www/nextcloud/occ" ]; then
    echo "Nextcloud is not installed on this server."
    echo "Skipping ONLYOFFICE/Nextcloud integration."
else

    read -r -p "Install ONLYOFFICE Docs and integrate it with Nextcloud? [y/N]: " INSTALL_ONLYOFFICE
    INSTALL_ONLYOFFICE="${INSTALL_ONLYOFFICE:-n}"

    if [[ "$INSTALL_ONLYOFFICE" =~ ^[Yy]$ ]]; then

        # ----------------------------------------------------
        # Resource checks
        # ----------------------------------------------------

        OO_RAM_MB="$(free -m | awk '/^Mem:/ {print $2}')"
        OO_SWAP_MB="$(free -m | awk '/^Swap:/ {print $2}')"
        OO_DISK_GB="$(df -BG / | awk 'NR==2 {gsub("G","",$4); print $4}')"

        echo
        echo "ONLYOFFICE resource check:"
        echo "  RAM:       ${OO_RAM_MB} MB"
        echo "  Swap:      ${OO_SWAP_MB} MB"
        echo "  Free disk: ${OO_DISK_GB} GB"
        echo

        if [ "$OO_RAM_MB" -lt 4000 ]; then
            die "ONLYOFFICE requires at least 4 GB RAM for this provisioner."
        fi

        if [ "$OO_SWAP_MB" -lt 3900 ]; then
            die "ONLYOFFICE requires approximately 4 GB swap for this provisioner."
        fi

        if [ "$OO_DISK_GB" -lt 40 ]; then
            die "ONLYOFFICE requires at least 40 GB free disk for this provisioner."
        fi

        if ! command_exists docker; then
            die "Docker is required for ONLYOFFICE."
        fi

        # ----------------------------------------------------
        # Determine hostnames
        # ----------------------------------------------------

        read -r -p "PACT hostname [pact.local]: " PACT_HOST
        PACT_HOST="${PACT_HOST:-pact.local}"

        read -r -p "Nextcloud hostname [cloud-test.local]: " OO_NEXTCLOUD_HOST
        OO_NEXTCLOUD_HOST="${OO_NEXTCLOUD_HOST:-cloud-test.local}"

        read -r -p "ONLYOFFICE hostname [office-test.local]: " OO_HOST
        OO_HOST="${OO_HOST:-office-test.local}"

        echo
        echo "PACT:       http://${PACT_HOST}"
        echo "Nextcloud:  http://${OO_NEXTCLOUD_HOST}"
        echo "ONLYOFFICE: http://${OO_HOST}"
        echo

        # ----------------------------------------------------
        # Persistent storage / JWT
        # ----------------------------------------------------

        section "Preparing ONLYOFFICE Storage"

        sudo mkdir -p \
            /opt/onlyoffice/logs \
            /opt/onlyoffice/data \
            /opt/onlyoffice/lib \
            /opt/onlyoffice/db

        if [ ! -f /opt/onlyoffice/jwt.secret ]; then
            sudo bash -c \
                'openssl rand -hex 32 > /opt/onlyoffice/jwt.secret'
            sudo chmod 600 /opt/onlyoffice/jwt.secret
            echo "Generated ONLYOFFICE JWT secret."
        else
            echo "Existing ONLYOFFICE JWT secret retained."
        fi

        # ----------------------------------------------------
        # Docker container
        # ----------------------------------------------------

        section "Installing ONLYOFFICE Document Server"

        sudo docker pull onlyoffice/documentserver

        OO_JWT_SECRET="$(sudo cat /opt/onlyoffice/jwt.secret)"
        OO_CREATE_CONTAINER="no"

        if sudo docker ps -a \
            --format '{{.Names}}' \
            | grep -qx 'onlyoffice-documentserver'; then

            echo "Existing ONLYOFFICE container found."

            OO_EXTRA_HOSTS="$(
                sudo docker inspect onlyoffice-documentserver \
                    --format '{{json .HostConfig.ExtraHosts}}' \
                    2>/dev/null || true
            )"

            OO_MAPPING_OK="yes"

            if [[ "$OO_EXTRA_HOSTS" != *"${OO_NEXTCLOUD_HOST}:host-gateway"* ]]; then
                echo "Missing Docker host mapping for Nextcloud: ${OO_NEXTCLOUD_HOST}"
                OO_MAPPING_OK="no"
            fi

            if [[ "$OO_EXTRA_HOSTS" != *"${PACT_HOST}:host-gateway"* ]]; then
                echo "Missing Docker host mapping for PACT: ${PACT_HOST}"
                OO_MAPPING_OK="no"
            fi

            if [ "$OO_MAPPING_OK" = "no" ]; then
                echo "Recreating ONLYOFFICE container with required host mappings."
                echo "Persistent ONLYOFFICE data and JWT secret will be retained."

                sudo docker rm -f onlyoffice-documentserver >/dev/null
                OO_CREATE_CONTAINER="yes"

            elif ! sudo docker ps \
                --format '{{.Names}}' \
                | grep -qx 'onlyoffice-documentserver'; then

                echo "Starting existing ONLYOFFICE container."
                sudo docker start onlyoffice-documentserver >/dev/null

            else
                echo "ONLYOFFICE container is already running with required host mappings."
            fi

        else
            OO_CREATE_CONTAINER="yes"
        fi

        if [ "$OO_CREATE_CONTAINER" = "yes" ]; then

            echo "Creating ONLYOFFICE Document Server container."

            sudo docker run -d \
                --name onlyoffice-documentserver \
                --restart=always \
                -p 127.0.0.1:8081:80 \
                --add-host="${OO_NEXTCLOUD_HOST}:host-gateway" \
                --add-host="${PACT_HOST}:host-gateway" \
                -e JWT_ENABLED=true \
                -e JWT_SECRET="$OO_JWT_SECRET" \
                -v /opt/onlyoffice/logs:/var/log/onlyoffice \
                -v /opt/onlyoffice/data:/var/www/onlyoffice/Data \
                -v /opt/onlyoffice/lib:/var/lib/onlyoffice \
                -v /opt/onlyoffice/db:/var/lib/postgresql \
                onlyoffice/documentserver >/dev/null
        fi
        echo "Waiting for ONLYOFFICE to initialize..."

        OO_READY="no"

        for i in $(seq 1 60); do
            if curl -fsS \
                http://127.0.0.1:8081/healthcheck \
                2>/dev/null | grep -qi 'true'; then
                OO_READY="yes"
                break
            fi

            sleep 5
        done

        if [ "$OO_READY" != "yes" ]; then
            unset OO_JWT_SECRET
            die "ONLYOFFICE did not become healthy within 5 minutes."
        fi

        echo "ONLYOFFICE Document Server is healthy."

        # ----------------------------------------------------
        # Apache reverse proxy
        # ----------------------------------------------------

        section "Configuring ONLYOFFICE Apache Proxy"

        sudo a2enmod \
            proxy \
            proxy_http \
            proxy_wstunnel \
            headers >/dev/null

        SAFE_OO_SITE="$(printf '%s' "$OO_HOST" | tr '.-' '__')"
        OO_APACHE_SITE="/etc/apache2/sites-available/${SAFE_OO_SITE}.conf"

        sudo tee "$OO_APACHE_SITE" >/dev/null <<APACHE
<VirtualHost *:80>
    ServerName ${OO_HOST}

    ProxyPreserveHost On
    ProxyRequests Off

    RequestHeader set X-Forwarded-Proto "http"
    RequestHeader set X-Forwarded-Host "${OO_HOST}"

    ProxyPassMatch "^/(.*)/websocket$" "ws://127.0.0.1:8081/\$1/websocket"

    ProxyPass        / http://127.0.0.1:8081/ retry=0 timeout=300
    ProxyPassReverse / http://127.0.0.1:8081/

    ErrorLog \${APACHE_LOG_DIR}/${SAFE_OO_SITE}_error.log
    CustomLog \${APACHE_LOG_DIR}/${SAFE_OO_SITE}_access.log combined
</VirtualHost>
APACHE

        sudo a2ensite "$(basename "$OO_APACHE_SITE")" >/dev/null

        if ! sudo apache2ctl configtest; then
            unset OO_JWT_SECRET
            die "Apache configuration failed after adding ONLYOFFICE."
        fi

        sudo systemctl reload apache2

        # ----------------------------------------------------
        # Local hostname resolution
        # ----------------------------------------------------

        section "Configuring Internal Hostnames"

        if ! grep -qE \
            "^[[:space:]]*127\.0\.0\.1[[:space:]].*${OO_NEXTCLOUD_HOST//./\\.}([[:space:]]|$)" \
            /etc/hosts; then
            echo "127.0.0.1 ${OO_NEXTCLOUD_HOST}" \
                | sudo tee -a /etc/hosts >/dev/null
        fi

        if ! grep -qE \
            "^[[:space:]]*127\.0\.0\.1[[:space:]].*${OO_HOST//./\\.}([[:space:]]|$)" \
            /etc/hosts; then
            echo "127.0.0.1 ${OO_HOST}" \
                | sudo tee -a /etc/hosts >/dev/null
        fi

        # Confirm container can reach Nextcloud.

        if ! sudo docker exec onlyoffice-documentserver \
            curl -fsSI "http://${OO_NEXTCLOUD_HOST}/" >/dev/null; then
            unset OO_JWT_SECRET
            die "ONLYOFFICE container cannot reach Nextcloud."
        fi

        # ----------------------------------------------------
        # Nextcloud connector
        # ----------------------------------------------------

        section "Installing Nextcloud ONLYOFFICE Connector"

        if ! sudo -u www-data php \
            --define apc.enable_cli=1 \
            /var/www/nextcloud/occ app:list \
            | grep -q 'onlyoffice:'; then

            sudo -u www-data php \
                --define apc.enable_cli=1 \
                /var/www/nextcloud/occ app:install onlyoffice
        fi

        sudo -u www-data php \
            --define apc.enable_cli=1 \
            /var/www/nextcloud/occ app:enable onlyoffice

        # Public browser-facing Document Server URL.

        sudo -u www-data php \
            --define apc.enable_cli=1 \
            /var/www/nextcloud/occ \
            config:app:set onlyoffice DocumentServerUrl \
            --value="http://${OO_HOST}/"

        # Internal Nextcloud -> Document Server URL.

        sudo -u www-data php \
            --define apc.enable_cli=1 \
            /var/www/nextcloud/occ \
            config:app:set onlyoffice DocumentServerInternalUrl \
            --value="http://${OO_HOST}/"

        # Internal Document Server -> Nextcloud callback/storage URL.

        sudo -u www-data php \
            --define apc.enable_cli=1 \
            /var/www/nextcloud/occ \
            config:app:set onlyoffice StorageUrl \
            --value="http://${OO_NEXTCLOUD_HOST}/"

        # Shared JWT secret.

        sudo -u www-data php \
            --define apc.enable_cli=1 \
            /var/www/nextcloud/occ \
            config:app:set onlyoffice jwt_secret \
            --value="$OO_JWT_SECRET"

        unset OO_JWT_SECRET

        # ----------------------------------------------------
        # End-to-end verification
        # ----------------------------------------------------

        section "Verifying ONLYOFFICE Integration"

        if sudo -u www-data php \
            --define apc.enable_cli=1 \
            /var/www/nextcloud/occ \
            onlyoffice:documentserver --check; then

            ONLYOFFICE_INSTALLED="yes"

            echo
            echo "ONLYOFFICE integration verified successfully."

            # Persist non-secret platform configuration for application installers.
            sudo tee /etc/pact-platform.conf >/dev/null <<EOF
PACT_HOST="${PACT_HOST}"
NEXTCLOUD_HOST="${OO_NEXTCLOUD_HOST}"
ONLYOFFICE_HOST="${OO_HOST}"
ONLYOFFICE_PORT="8081"
EOF

            sudo chmod 644 /etc/pact-platform.conf
            echo "Platform configuration saved to /etc/pact-platform.conf."
            echo
            echo "Nextcloud:"
            echo "  http://${OO_NEXTCLOUD_HOST}"
            echo
            echo "ONLYOFFICE:"
            echo "  http://${OO_HOST}"
            echo
        else
            die "Nextcloud could not verify the ONLYOFFICE Document Server."
        fi

    else
        echo "Skipping ONLYOFFICE."
    fi
fi

# ------------------------------------------------------------
# 14. Final verification
# ------------------------------------------------------------

section "Provisioning Summary"

echo "Operating System:"
echo "  ${PRETTY_NAME:-Ubuntu}"
echo

echo "Apache:"
apache2ctl -v | head -1
echo

echo "PHP:"
php -v | head -1
echo

echo "PHP extensions:"
for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if php -m | grep -qi "^${ext}$"; then
        printf "  %-12s OK\n" "$ext"
    else
        printf "  %-12s MISSING\n" "$ext"
    fi
done
echo

echo "MySQL:"
mysql --version
echo

echo "Composer:"
composer --version
echo

echo "Git:"
git --version
echo

echo "LibreOffice:"
if command_exists libreoffice; then
    libreoffice --version
else
    echo "  NOT FOUND"
fi
echo

echo "Docker:"
docker --version
echo

echo "Resources:"
echo "  RAM:            ${TOTAL_RAM_MB} MB"
echo "  Root free disk: ${AVAILABLE_DISK_GB} GB"
echo "  Swap:           ${SWAP_MB} MB"
echo

echo "Platform services:"

if [ -f /var/www/nextcloud/occ ]; then
    if sudo -u www-data php --define apc.enable_cli=1 \
        /var/www/nextcloud/occ status >/dev/null 2>&1; then
        echo "  Nextcloud                     INSTALLED / OK"
    else
        echo "  Nextcloud                     INSTALLED / CHECK REQUIRED"
    fi
else
    echo "  Nextcloud                     NOT INSTALLED"
fi

if sudo docker ps --format '{{.Names}}' 2>/dev/null \
    | grep -qx 'onlyoffice-documentserver'; then
    echo "  ONLYOFFICE Docs               RUNNING"

    if [ -f /var/www/nextcloud/occ ]; then
        if sudo -u www-data php --define apc.enable_cli=1 \
            /var/www/nextcloud/occ \
            onlyoffice:documentserver --check >/dev/null 2>&1; then
            echo "  Nextcloud <-> ONLYOFFICE      CONNECTED / OK"
        else
            echo "  Nextcloud <-> ONLYOFFICE      CHECK REQUIRED"
        fi
    fi
else
    if sudo docker ps -a --format '{{.Names}}' 2>/dev/null \
        | grep -qx 'onlyoffice-documentserver'; then
        echo "  ONLYOFFICE Docs               INSTALLED / NOT RUNNING"
    else
        echo "  ONLYOFFICE Docs               NOT INSTALLED"
    fi
fi

echo
echo "============================================================"
echo " Server provisioning completed successfully."
echo "============================================================"
echo

if [ "$DOCKER_GROUP_ADDED" = "yes" ]; then
    echo "NOTE:"
    echo "Your user was added to the docker group."
    echo "Log out and back in before using Docker without sudo."
    echo
fi

echo "PACT application installation is handled separately."
echo "Run ./install.sh when you are ready to install PACT."
echo
