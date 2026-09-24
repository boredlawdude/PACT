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
# 12. Final verification
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

echo "============================================================"
echo " Base server provisioning completed successfully."
echo "============================================================"
echo

if [ "$DOCKER_GROUP_ADDED" = "yes" ]; then
    echo "NOTE:"
    echo "Your user was added to the docker group."
    echo "Log out and back in before using Docker without sudo."
    echo
fi

echo "This server is now ready for:"
echo
echo "  1. Nextcloud provisioning"
echo "  2. ONLYOFFICE Docs provisioning"
echo "  3. PACT installation"
echo
echo "PACT itself should be installed separately using install.sh."
echo
