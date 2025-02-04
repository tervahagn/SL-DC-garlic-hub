# garlic-hub - Digital Signage Management Platform

Garlic-Hub will become a robust Digital Signage Management Platform designed to handle the core tasks of a CMS and extend into advanced device management. From single-screen setups to distributed signage networks, Garlic-Hub offers centralized control and flexibility for various signage environments.

> **Note**: Garlic-Hub is currently in heavily development.Core functionalities are still under construction, with some 
> modules incomplete or in early testing stages. This version is not recommended for production use. Feedback and contributions are appreciated as we refine the platform for stability and a full-featured release.

## Installation

### Prerequisites
Ensure your environment meets the following requirements:
- **PHP** 8.3 or higher
- **Composer** for dependency management
- **Web Server**: Apache, Nginx, or other PHP-compatible server
- **Database**: MariaDB or SQLite for smaller deployments (compatible with other SQL databases)
- **Docker** (optional) for local development with ddev
 
### Step-by-Step Guide

1. **Clone the Repository**
```bash
   git clone https://github.com/sagiadinos/garlic-hub.git
   cd garlic-hub
```
### For Developer: Install ddev (docker)

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
Create directories configure the database and more:
```bash
php install.php
```
The login will be created with the following credentials:
- **login**: admin
- **password**: thymian

## Development Roadmap

Garlic-Hub is being developed in a phased approach to ensure stability and optimal performance across different usage scenarios. The platform will be rolled out in three primary editions, each tailored to distinct needs:

1. **Edge Edition (Phase 1)**  
   The first phase focuses on the Edge Edition, designed for single-device deployments or small setups. This edition will feature reduced CMS functions, including media management and SMIL-based playlist creation, with a lightweight architecture. Target is to bundle it with garlic-player for IoT devices like Raspberry Pi or Android-based media players.
2. **Core Edition (Phase 2)**  
   The Core Edition is aimed at mid-sized networks or NAS. It will manage up to a limited amount media players. This phase will introduce some more extended management features, improved reporting capabilities, and a more robust device management layer. Core will be tailored for businesses with small networks and limited hardware.
3. **Enterprise Edition (Phase 3)**  
   In the final phase, Garlic-Hub will evolve into an enterprise-grade platform, supporting large-scale digital signage networks. The Enterprise Edition will offer advanced features such as full SaaS/on-premise hosting with role-based permissions, and enhanced analytics. This edition will cater to companies needing scalable, comprehensive digital signage solutions for diverse locations.

Each phase will build mostly on the features of the previous editions, ensuring a smooth upgrade path as Garlic-Hub grows into a free and modern digital signage management solution based on SMIL multimedia language.

## Features

The following features are under active development and subject to change as Garlic-Hub progresses toward its first stable release:

### Current Development in phase 1 

Probably ready at May 2025. But this depends on my real life, time schedule.

- **Basics**: Database, db migration, logging, routing, middleware and error handling using SLIM 4 framework
  OAuth2 (completed)
- **Login/ Authentication**: Simple session based password login with remember me function and token based 
  authorization via OAuth2 (completed) 
- **Mediapool**: Organize multimedia content with tree folders in a central place. Uploads from different sources 
  including local files, external links, screencasts, camera and stock platforms like  Pixabay, Unsplash and Pexels 
  (90% complete)
- **SMIL-Based Playlists**: Organize playlists with a playlist manager. and export them in the 
  SMIL (Synchronized Multimedia Integration Language) format, an industry standard for multimedia presentations, ensuring compatibility with a wide range of media players.
- **Multi-Language Support**: Offers locale-specific configurations and an adaptable UI to support diverse audiences.
- **Multi-Zone Content**: Define and control display zones with customizable templates, allowing complex content layouts.
- **Player** supports one local media player

### Planned Features
- **Device Management**: Remotely manage digital signage players, including configuration, updates, and monitoring.
- **Real-Time Reporting**: Collect logs from connected media players, generate insightful reports, and monitor system health.
- **Templating**: SVG and HTML Template-Engine.
- **Feeds**: Organizing Feeds like RSS and more.
- **Channels**: Creating automated content based on feeds and templates. 
- **Flexible Configuration**: Designed to run across multiple device types, such as Raspberry Pi or Android-based media players.
- **Scalable Architecture**: Available in editions tailored to different scales – from local installations to cloud-hosted enterprise solutions.

## Documentation
- [Coding Standards](docs%2Fcoding-standards.md)
- [Exceptions](docs%2Fexceptions.md)
- [DI-Container](docs%2Fdi-container.md)
- [SQL Repository Usage](docs%2Fsql-repository-usage.md)
- [CLI.php - Command Line Interface](docs%2Fcli.md)
- [Api/Oauth2 - API and Oauth2](docs%2Foauth2.md)
- [User- Administration](docs%2Fuser-administration.md)

# Contributing
Given its early development stage, contributions are highly encouraged. Please note that code changes, features, and documentation are subject to change as the project evolves toward a production-ready state.

# License
Garlic-Hub is open-source software licensed under the Affero GPL v3.0 License.
