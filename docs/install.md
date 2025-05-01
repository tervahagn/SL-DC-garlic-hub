# Installation instructions

## For Developer

### Prerequisites
Ensure your environment meets the following requirements:
- **PHP** 8.3 or higher
- **Composer** for dependency management
- **Web Server**: Apache, Nginx, or other PHP-compatible server
- **Database**: MariaDB or SQLite for smaller deployments (compatible with other SQL databases)
- **Docker** (optional) for local development with ddev

#### Debian

Packages needed:
```bash
apt install curl php-http php-zip php-simplexml php-curl php-intl php-imagick php-cli php-mbstring git unzip
```

### Step-by-Step Guide

1. **Clone the Repository**
```bash
   git clone https://github.com/sagiadinos/garlic-hub.git
   cd garlic-hub
```
### For Developer: Install ddev

Find a docker provider for your Operating System, open a terminal in your IDE. If you like customize the ./ddev/config.yaml file to your needs, but it is not required.

```bash
ddev start
```
### Install PHP Dependencies
Run Composer to install required PHP packages:

```php
composer install
```

### Set Up Environment Variables
Copy the example environment file and update it as needed:

```bash
cp .env.dist .env
```
### Installer
Create directories, configure the database and more:
```bash
bash install.sh
```
The login will be created with the following credentials:
- **login**: admin
- **password**: thymian

## Create and Use Docker Image

Assuming you have already installed Docker Compose.

```bash
   git clone https://github.com/sagiadinos/garlic-hub.git
   cd garlic-hub
   docker compose up --build 
```
Then you need to open a web browser and go to http://localhost:8090

### Remarks

This is currently a testing release and is subject to change. 

There are two `var` directories: one located in the system root and another in the `htdocs` root (public).

At present, garlic-hub uses host-mounted volumes (bind mounts), meaning directories are created in the location where you start the container.

Future releases will transition to Docker-managed named volumes.