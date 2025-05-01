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

This is only a testing release. Things may change 

There are two var directories. One in the system root and the other in the htdoc root (public).   

Currently, garlic-hub use host-mounted volumes (bind mounts). That means the dir where created in the directory you start the container.

In future we will use Docker-managed  so-called named volumes.