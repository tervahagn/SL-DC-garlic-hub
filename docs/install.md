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
bash dev-install.sh
```
The login will be created with the following credentials:
- **login**: admin
- **password**: thymian

## Work with a Docker Image

Assuming you have already installed Docker Compose and that, you are somehow familiar with Docker.

#### Create a Docker Image and Run

```bash
   git clone https://github.com/sagiadinos/garlic-hub.git
   cd garlic-hub
   docker compose up --build 
```
Then you need to open a web browser and go to http://localhost:8090

#### Downloaded Image from Docker hub

```bash
docker run -p 8090:80 --name garlic-hub-container -v garlic-hub-public-var:/var/www/public/var -v garlic-hub-var:/var/www/var sagiadinos/garlic-hub:latest
```
Then you need also to open a web browser and go to http://localhost:8090

You can start, stop or restart the container later with:

```bash
docker start|stop|restart garlic-hub-container
```

### Admin User

There is an admin user created:
login: admin
password: thymian

### Remarks

There are two `var` directories: one located in the system root and another in the `htdocs` root (public).
These are Docker managed named volumes.

This is a testing release and is subject to change. This means: there is no update migration. You need to delete volumes, container, and images when you want to install a newer version. 
Do not use it in productive enviroment.

