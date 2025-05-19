[![docker-hub image](https://github.com/sagiadinos/garlic-hub/actions/workflows/docker-image.yml/badge.svg?branch=main)](https://github.com/sagiadinos/garlic-hub/actions/workflows/docker-image.yml)
[![garlic-hub coverage](https://github.com/sagiadinos/garlic-hub/blob/main/misc/coverage.svg)](https://github.com/sagiadinos/garlic-hub/blob/main/misc/coverage.svg)

# garlic-hub - Smart Digital Signage Management

Garlic-Hub is a Digital Signage Management Software which handle the core tasks of a CMS together with device management. From single-screen setups to distributed signage networks, Garlic-Hub offers centralized control and flexibility for various signage environments.

> **Note**: Garlic-Hub is currently in heavily development. Core functionalities are still under construction, with some 
> modules incomplete or in early testing stages. This version is not recommended for production use. Feedback and contributions are appreciated as we refine the platform for stability and a full-featured release.

## Installation Instructions for Developer and User

[install.md](docs/install.md)

## Development Roadmap

Garlic-Hub is being developed in a phased approach. The software will be rolled out in three primary editions, each tailored to distinct needs:

1. **Edge Edition (Phase 1)**  
   The first phase focuses on the Edge edition, designed for single-device deployments or small setups. This edition will feature reduced CMS functions, including media management and SMIL-based playlist creation, with a lightweight architecture. Target is to bundle it with garlic-player for IoT media player devices or simply use it in small intranets with extreme low-cost servers like Raspberry PI.
2. **Core Edition (Phase 2)**  
   The Core Edition is aimed at mid-sized networks or NAS. It will manage up to a limited amount media players. This phase will introduce smoer device management and content creation feature. The Core edition will be tailored for businesses with small networks and limited hardware.
3. **Enterprise Edition (Phase 3)**  
   In the final phase, Garlic-Hub will evolve into an enterprise-grade platform, supporting large-scale digital signage networks. The Enterprise Edition will offer advanced features such as full SaaS/on-premise hosting with role-based permissions, license handling, improved reporting capabilities, enhanced analytics etc. This edition will cater to companies needing scalable, comprehensive digital signage solutions.

Each phase will build mostly on the features of the previous editions, ensuring a smooth upgrade path as Garlic-Hub grows into a free and modern digital signage management solution based on SMIL multimedia language.

## Features

The following features are under active development and subject to change as Garlic-Hub progresses toward its first stable release:

### Current Development in phase 1 

At end of May 2025. Currently, Edge is feature complete and we are in testing period.

- **Basics**: Database, db migration, logging, routing, middleware and error handling using SLIM 4 framework
  OAuth2 (completed)
- **Login/ Authentication**: Simple session based password login with remember me function and token based 
  authorization via OAuth2 (completed) 
- **Mediapool**: Organize multimedia content with tree folders in a central place. Uploads from different sources 
  including local files, external links, screencasts, camera, stock platforms (Pixabay, Unsplash, Pexels)
  (completed)
- **SMIL-Based Playlists**: Organize playlists with a playlist manager. and export them in the 
  SMIL (Synchronized Multimedia Integration Language) format, an industry standard for multimedia presentations, ensuring compatibility with a wide range of media players (completed).
- **Multi-Language Support**: Offers locale-specific configurations and an adaptable UI to support diverse audiences. (englisch, german in progress)
- **Multi-Zone Content**: Define and control display zones with customizable templates, allowing complex content layouts. (completed)
- **Player** supports one local media player (completed)

### Planned Features
- **Device Management**: Remotely manage digital signage players, including configuration, updates, and monitoring.
- **Real-Time Reporting**: Collect logs from connected media players, generate insightful reports, and monitor system health.
- **Templating**: SVG and HTML Template-Engine.
- **Feeds**: Organizing Feeds like RSS and more.
- **Channels**: Creating automated content based on feeds and templates. 
- **Flexible Configuration**: Designed to run across multiple device types, such as Raspberry Pi or Android-based media players.
- **Scalable Architecture**: Available in editions tailored to different scales – from local installations to cloud-hosted enterprise solutions.

### Tech Stack
- PHP 8.3
- SLIM4 Framework
- some composer libs
- phpunit 11 for unit testing. Target is > 95 % coverage with unit tests.

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
