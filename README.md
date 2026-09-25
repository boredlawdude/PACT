# PACT
# (c) 2026 John P. Schifano (john@schifano.com) 
## Procurement(and/or Project) and Contract Tracking

PACT is a web-based municipal  contract management platform
built with PHP and MySQL.

The application is designed to run on an Ubuntu Linux server and can be
deployed together with Apache, PHP, MySQL, Composer, LibreOffice,
Nextcloud, ONLYOFFICE Docs, and Docker.

The repository includes two primary installation scripts:

-   `provision-server.sh` --- prepares an Ubuntu server and installs the
    supporting platform.
-   `install.sh` --- installs and configures the PACT application.

The intended deployment sequence is:

``` text
Ubuntu
   |
   +-- provision-server.sh
   |      +-- Apache / PHP
   |      +-- MySQL
   |      +-- Composer
   |      +-- LibreOffice
   |      +-- Docker
   |      +-- Nextcloud
   |      +-- ONLYOFFICE Docs
   |
   +-- install.sh
          +-- PACT database
          +-- Reference data
          +-- .env
          +-- Administrator
          +-- Apache virtual host
          +-- ONLYOFFICE integration
```

## 1. Recommended Server

The currently tested configuration is:

-   Ubuntu Server 26.04 LTS
-   6 virtual CPUs
-   6 GB RAM
-   4 GB swap
-   100 GB system disk
-   Network access to the organization's LAN
-   SSH access for administration

PACT itself requires substantially fewer resources than this. The
recommended configuration primarily accommodates Nextcloud and
ONLYOFFICE Docs on the same server.

For production deployments, storage should be sized according to
anticipated document volume.

## 2. Ubuntu Installation

A minimal Ubuntu Server installation is recommended.

During Ubuntu installation:

-   Install OpenSSH Server.
-   A minimized installation is acceptable.
-   Featured server snaps are not required.
-   Configure a static address or DHCP reservation where appropriate.

After installation, log into the server using the normal administrative
user.

**Do not run the PACT installation scripts by logging in as root.** The
scripts request `sudo` privileges when required.

## 3. Verify Disk Space

Before provisioning:

``` bash
df -h /
lsblk
sudo vgs
sudo lvs
```

Ubuntu installations using LVM may create a logical root volume
substantially smaller than the virtual disk even though unused space
remains in the volume group.

The PACT provisioner intentionally does **not** automatically resize LVM
volumes because disk-layout changes should not be performed
automatically.

If this is a new server, the volume group contains unused space, and you
have confirmed that the root logical volume should use the remaining
space, an administrator may expand it manually. For the standard Ubuntu
layout this may be:

``` bash
sudo lvextend -l +100%FREE -r /dev/ubuntu-vg/ubuntu-lv
```

Do not run that command without first verifying the actual LVM
configuration with `vgs`, `lvs`, and `lsblk`.

## 4. Verify Swap

Check available swap:

``` bash
free -h
swapon --show
```

The tested configuration uses approximately 4 GB of swap. If ONLYOFFICE
is selected, `provision-server.sh` performs resource checks before
installation.

## 5. Install Git

Git may be the only package that needs to be installed manually before
cloning PACT:

``` bash
sudo apt update
sudo apt install -y git
```

## 6. GitHub Deployment Key

For deployed servers, a read-only GitHub deploy key is recommended.

Generate a key:

``` bash
ssh-keygen -t ed25519 -f ~/.ssh/id_ed25519_pact -C "PACT deployment key"
```

Display the public key:

``` bash
cat ~/.ssh/id_ed25519_pact.pub
```

Add that public key to the PACT GitHub repository as a Deploy Key. For a
normal deployment server, leave **Allow write access** disabled.

Create or edit `~/.ssh/config`:

``` text
Host github.com-pact
    HostName github.com
    User git
    IdentityFile ~/.ssh/id_ed25519_pact
    IdentitiesOnly yes
```

Set permissions:

``` bash
chmod 700 ~/.ssh
chmod 600 ~/.ssh/config
chmod 600 ~/.ssh/id_ed25519_pact
chmod 644 ~/.ssh/id_ed25519_pact.pub
```

Test access:

``` bash
ssh -T git@github.com-pact
```

## 7. Clone PACT

``` bash
sudo mkdir -p /var/www/contracts_app
sudo chown "$(id -un)":"$(id -gn)" /var/www/contracts_app
git clone git@github.com-pact:boredlawdude/PACT.git /var/www/contracts_app
cd /var/www/contracts_app
```

## 8. Provision the Server

Run:

``` bash
./provision-server.sh
```

Run this as the normal administrative user. Do **not** use
`sudo ./provision-server.sh`; the provisioner uses `sudo` internally
where elevated privileges are required.

The provisioner installs and/or configures the required server
components, including Apache, PHP and required extensions, MySQL,
Composer, LibreOffice, Docker, cron, and supporting utilities.

The provisioner can also install Nextcloud and ONLYOFFICE Docs.

## 9. Hostnames

When ONLYOFFICE is installed, the provisioner requests three hostnames.

Example development/test values:

``` text
PACT hostname:       pact.local
Nextcloud hostname:  cloud-test.local
ONLYOFFICE hostname: office-test.local
```

These are examples only. Production deployments should normally use
hostnames supplied by the organization's DNS infrastructure.

The provisioner records non-secret platform configuration in:

``` text
/etc/pact-platform.conf
```

This includes `PACT_HOST`, `NEXTCLOUD_HOST`, `ONLYOFFICE_HOST`, and
`ONLYOFFICE_PORT`. Secrets are not stored in this configuration file.

## 10. Nextcloud

If Nextcloud installation is selected, the provisioner installs required
PHP modules, Redis and supporting packages; downloads and installs
Nextcloud; creates its database; configures the data directory, Apache,
APCu, Redis, and cron; and verifies the installation.

Default locations:

``` text
Application: /var/www/nextcloud
Data:        /var/nextcloud-data
```

The Nextcloud administrator password is entered interactively and should
not be stored in the repository.

## 11. ONLYOFFICE Docs

If selected, the provisioner installs ONLYOFFICE Document Server using
Docker.

Persistent data is stored under:

``` text
/opt/onlyoffice/logs
/opt/onlyoffice/data
/opt/onlyoffice/lib
/opt/onlyoffice/db
```

The JWT secret is stored separately at:

``` text
/opt/onlyoffice/jwt.secret
```

The JWT secret must never be committed to Git.

The container is named `onlyoffice-documentserver` and is bound locally
to `127.0.0.1:8081`. Apache acts as the web-facing proxy.

## 12. Docker Hostname Routing

ONLYOFFICE must be able to communicate with both Nextcloud and PACT. The
Docker container therefore receives host mappings for both services
through Docker's host gateway.

This is required because PACT supplies ONLYOFFICE with document and
callback URLs, such as:

``` text
http://pact.local/onlyoffice_callback.php
```

If the ONLYOFFICE container cannot resolve the PACT hostname, the editor
may load but remain at **Loading document** and eventually report **The
file cannot be accessed right now**.

Diagnostic example:

``` bash
docker exec onlyoffice-documentserver getent hosts pact.local
```

Replace `pact.local` with the actual PACT hostname.

When rerun, the provisioner checks the required mappings. A correctly
configured existing container is retained. If required mappings are
missing, the container is recreated while the persistent
`/opt/onlyoffice` data and existing JWT secret are retained.

## 13. Install PACT

After provisioning:

``` bash
cd /var/www/contracts_app
./install.sh
```

Again, run the script as the normal administrative user.

The installer handles Composer dependencies, storage directories,
database creation and privileges, schema installation, reference-data
seeding, `.env` configuration, initial SUPERUSER creation, organization
configuration, Apache virtual-host configuration, ONLYOFFICE settings,
and installation verification.

## 14. Database

A typical installation uses:

``` text
Database:                 contract_manager
Application database user: contract_user
```

The actual database password should be entered interactively and must
not be committed to Git.

On a fresh database, the installer imports:

``` text
database/schema.sql
database/reference_seed.sql
```

The reference seed contains required system/reference data. It does not
contain production contracts, vendors, users, operational documents, or
history.

## 15. Existing Installations and Reruns

`install.sh` is designed to be safely rerunnable.

When an existing database is detected, the installer does not blindly
recreate the schema or reference data. When an existing `.env` is
detected, the installer offers to preserve it.

If a provisioned ONLYOFFICE installation is detected, the installer can
update only the required ONLYOFFICE settings while preserving the
remaining `.env` configuration.

The installer also detects an existing active SUPERUSER and does not
recreate or reset that administrator account during a normal rerun.

Before modifying an existing `.env`, the installer creates a timestamped
backup.

## 16. Environment File

PACT's environment file is:

``` text
/var/www/contracts_app/.env
```

It may contain credentials and secrets. It is excluded from Git and
should never be committed.

Typical settings include:

``` text
APP_NAME
APP_ENV
APP_URL
DB_HOST
DB_PORT
DB_NAME
DB_USER
DB_PASS
SMTP_HOST
SMTP_PORT
SMTP_SECURE
SMTP_USERNAME
SMTP_PASSWORD
ONLYOFFICE_DOCUMENT_SERVER_URL
ONLYOFFICE_APP_BASE_URL
ONLYOFFICE_JWT_SECRET
OO_SECRET
NEXTCLOUD_BASE_URL
NEXTCLOUD_WEBDAV_ROOT
```

## 17. Client DNS / Host Resolution

Every client browser using PACT and ONLYOFFICE must be able to resolve
the service hostnames.

For a test environment without DNS, temporary entries may be added to
the client's hosts file:

``` text
192.168.1.50 pact.local
192.168.1.50 cloud-test.local
192.168.1.50 office-test.local
```

Use the actual server address.

On macOS, the hosts file is `/etc/hosts`. After changing it:

``` bash
sudo dscacheutil -flushcache
sudo killall -HUP mDNSResponder
```

Test resolution:

``` bash
dscacheutil -q host -a name pact.local
dscacheutil -q host -a name office-test.local
```

Avoid duplicate host entries pointing the same hostname to different
addresses. macOS may resolve an old address even when a newer entry is
also present.

## 18. A Note About `.local`

`.local` names are convenient for development but are not recommended as
the long-term naming scheme for production deployments.

macOS and other systems use `.local` for multicast DNS (mDNS/Bonjour),
which can produce unexpected name-resolution behavior.

Production deployments should preferably use properly configured
organizational DNS names.

## 19. Remote Administration / Tailscale

Tailscale can be useful for remote server administration and testing. A
remote administrator can map test hostnames to the server's Tailscale
address in the administrator's local hosts file.

Normal users on the organization's LAN should generally access PACT,
Nextcloud, and ONLYOFFICE through local network addressing and DNS.

Remote VPN/Tailscale performance should not be used as the benchmark for
normal LAN performance.

## 20. Functional Verification

After installation, open the configured PACT URL and log in with the
SUPERUSER account created during installation.

Verify at least:

-   Dashboard loads.
-   Contracts can be created.
-   Contract documents can be generated.
-   Existing documents can be downloaded.
-   Inline ONLYOFFICE editor loads.
-   A DOCX opens in ONLYOFFICE.
-   Changes can be saved.
-   Nextcloud is reachable if installed.

## 21. Verify ONLYOFFICE

Check the container:

``` bash
docker ps --filter name=onlyoffice-documentserver
```

Check health:

``` bash
curl http://127.0.0.1:8081/healthcheck
```

Expected:

``` text
true
```

Check the browser-facing API through Apache:

``` bash
curl -I http://office-test.local/web-apps/apps/api/documents/api.js
```

Replace the hostname as appropriate. A successful request should return
HTTP 200.

Check that ONLYOFFICE can resolve PACT:

``` bash
docker exec onlyoffice-documentserver getent hosts pact.local
```

## 22. ONLYOFFICE Troubleshooting

### "OnlyOffice API script did not load"

If PACT reports:

``` text
OnlyOffice API script did not load. Check ONLYOFFICE_DOCUMENT_SERVER_URL and office proxy.
```

verify that the browser can reach:

``` text
http://<ONLYOFFICE_HOST>/web-apps/apps/api/documents/api.js
```

Then test from the server:

``` bash
curl -I http://127.0.0.1:8081/web-apps/apps/api/documents/api.js
```

If the server succeeds but the browser fails, investigate client DNS,
`/etc/hosts`, firewall, VPN, or routing.

### "The file cannot be accessed right now"

If the ONLYOFFICE interface loads but the document remains at **Loading
document**, inspect:

``` bash
docker logs --tail 100 onlyoffice-documentserver
```

An error such as:

``` text
getaddrinfo ENOTFOUND pact.local
```

means the ONLYOFFICE container cannot resolve the PACT hostname.

Verify with:

``` bash
docker exec onlyoffice-documentserver getent hosts pact.local
```

The current provisioner automatically configures this mapping.

## 23. Apache Diagnostics

Show virtual hosts:

``` bash
sudo apache2ctl -S
```

Test configuration:

``` bash
sudo apache2ctl configtest
```

Reload:

``` bash
sudo systemctl reload apache2
```

A warning that Apache could not reliably determine the server's fully
qualified domain name does not by itself indicate that a PACT virtual
host is broken.

## 24. Updating PACT

Before updating a deployed server:

``` bash
cd /var/www/contracts_app
git status
```

Then:

``` bash
git pull
```

If platform changes are included:

``` bash
./provision-server.sh
```

If application-installation changes are included:

``` bash
./install.sh
```

Both scripts are intended to preserve existing configuration and data
where appropriate, but normal production backup practices should always
be followed before significant upgrades.

## 25. Security

At minimum:

-   Use SSH keys for server administration.
-   Use a read-only GitHub deploy key on deployed servers.
-   Do not commit `.env`.
-   Do not commit the ONLYOFFICE JWT secret.
-   Do not store production passwords in scripts.
-   Restrict administrative access.
-   Keep Ubuntu security updates current.
-   Keep PACT and supporting applications updated.
-   Use HTTPS for production deployments.
-   Back up the PACT database.
-   Back up PACT document storage.
-   Back up Nextcloud data if used.
-   Back up required application configuration.
-   Protect backups appropriately.

Production municipal deployments should be reviewed with the
organization's IT/security staff before being exposed to users or
external networks.

## 26. Important Files and Locations

  Purpose                         Location
  ------------------------------- --------------------------------
  PACT application                `/var/www/contracts_app`
  PACT environment                `/var/www/contracts_app/.env`
  Platform configuration          `/etc/pact-platform.conf`
  Nextcloud application           `/var/www/nextcloud`
  Nextcloud data                  `/var/nextcloud-data`
  ONLYOFFICE persistent storage   `/opt/onlyoffice`
  ONLYOFFICE JWT secret           `/opt/onlyoffice/jwt.secret`
  Apache available sites          `/etc/apache2/sites-available`
  Apache enabled sites            `/etc/apache2/sites-enabled`

## 27. Deployment Philosophy

PACT separates platform provisioning from application installation.

`provision-server.sh` is responsible for the operating-system and
supporting-service layer. `install.sh` is responsible for PACT itself.

This separation allows PACT to be deployed on a clean Ubuntu server,
platform provisioning to be rerun after infrastructure changes, the
application installer to be rerun without rebuilding the server,
application data and secrets to be preserved, and standardized municipal
server appliances to be maintained consistently.

## 28. Tested Deployment

This process has been tested using a clean Ubuntu Server 26.04 LTS
virtual machine with:

``` text
6 vCPU
6144 MB RAM
4 GB swap
100 GB system disk
```

The tested deployment successfully installed and operated PACT, Apache,
PHP, MySQL, Composer, LibreOffice, Docker, Nextcloud, and ONLYOFFICE
Docs.

Functional testing included successful PACT login, contract/document
creation, ONLYOFFICE API loading, PACT document retrieval by ONLYOFFICE,
and inline DOCX editing.
