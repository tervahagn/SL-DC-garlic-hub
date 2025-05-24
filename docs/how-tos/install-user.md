# Installation instructions

These instructions will help you with the installation and initial setup of the system.

Garlic-Hub can be installed in two ways:

1. By creating your own Docker image
2. By using a pre-built image from Docker Hu

Choose the method that best suits your requirements. Both options are quick and easy to set up.

After installation, you can log in with the default administrator account and start configuration.

**Note:** This version is a testing release and should not be used in production environments.

## Using Docker for Installation

### Prerequisites
- Docker Compose installed on your system
- Basic familiarity with Docker commands
- An internet connection to clone the repository

### Building and Running with Docker Compose
```bash
git clone https://github.com/sagiadinos/garlic-hub.git
cd garlic-hub
docker compose up -d --build
```

### Accessing the Application
After the container is successfully built and running, open a web browser and navigate to:
http://localhost:8090

## Installing from Docker Hub

To install Garlic-Hub using the pre-built Docker Hub image:

1. Run the container with necessary volume mounts:
```bash
docker run -p 8090:80 --name garlic-hub-container -v garlic-hub-public-var:/var/www/public/var -v garlic-hub-var:/var/www/var sagiadinos/garlic-hub:latest
```
2. Access the application by opening http://localhost:8090 in your web browser

## Container Management

After installation, you can manage the container with standard Docker commands:
```bash
# Start the container
docker start garlic-hub-container

# Stop the container
docker stop garlic-hub-container

# Restart the container
docker restart garlic-hub-container
```

## Admin User

There is an admin user created:

login: admin

password: thymian

## Docker Volume Structure

There are two `var` directories used as Docker managed named volumes:
- One located in the system root
- Another in the `htdocs` root (public)

## Important Notice

> **⚠️ WARNING: Testing Release Only**
>
> This is a testing release and is subject to change. Please note the following limitations:
> - No update migration is provided
>
> **DO NOT use in production environments.**


