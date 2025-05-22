~~[![docker-hub image](https://github.com/sagiadinos/garlic-hub/actions/workflows/docker-image.yml/badge.svg?branch=main)](https://github.com/sagiadinos/garlic-hub/actions/workflows/docker-image.yml)
[![garlic-hub coverage](https://github.com/sagiadinos/garlic-hub/blob/main/misc/coverage.svg)](https://github.com/sagiadinos/garlic-hub/blob/main/misc/coverage.svg)

# Garlic-Hub: Smart Digital Signage Management

Garlic-Hub is a comprehensive Digital Signage Management solution that handles core CMS tasks alongside device management. From single-screen setups to distributed networks, it provides centralized control with flexibility for various signage environments.

> **Note**: Garlic-Hub is currently under active development. Core functionalities are still under construction, with some modules incomplete or in early testing stages. This version is not recommended for production use. Feedback and contributions are welcome as we refine the platform.

## Quick Start

- [Installation Guide](docs/install.md)
- [Connecting Media Players](docs/how-tos/connect-mediaplayer.md)

## Project Overview

### Edition Roadmap

Garlic-Hub is being developed in three phases, each delivering a distinct edition:

| Edition | Target Environment                 | Key Features                                                             | Status |
|---------|------------------------------------|--------------------------------------------------------------------------|--------|
| **Edge** (Phase 1) | Single-device or small deployments | Basic media management, SMIL playlist creation, lightweight architecture | ✅ Feature complete, in testing |
| **Core** (Phase 2) | Mid-sized networks, NAS            | Limited device management, enhanced content creation                     | 🔄 Planned |
| **Enterprise** (Phase 3) | Large-scale networks               | SaaS/on-premise, role-based permissions, advanced analytics              | 🔄 Planned |

Each edition builds upon previous features, ensuring a smooth upgrade path as Garlic-Hub evolves into a comprehensive, SMIL-based digital signage solution.

### Current Features (Edge Edition)

- **Core Framework** ✅ - Database, migrations, logging, routing, middleware and error handling with SLIM 4
- **Authentication** ✅ - Session-based login with remember-me functionality and basic OAuth2 token authorization
- **Media Management** ✅ - Hierarchical content organization with multi-source uploads (local, external links, screencasts, camera, stock platforms with API-key)
- **SMIL Playlists** ✅ - Playlist management and export in industry-standard SMIL format
- **Multi-Zone Content** ✅ - Display zone control with customizable templates for complex layouts
- **Local Player Support** ✅ - Integration with one local media player
- **Internationalization** ✅ - Locale-specific configurations and adaptable UI (English complete, German complete)

### Coming Soon
- Online documentation
- Trigger based on time, events, touch, keys, and network
- Conditional play
- Device management for remote configuration and monitoring
- Real-time reporting and system health monitoring
- Image templating engine
- Raspberry Pi Player / CMS Bundle
- Scalable deployment options

## Technical Details

### Stack
- PHP 8.3
- SLIM 4 Framework
- Selected composer libraries
- PHPUnit 11 (targeting >95% test coverage)
- no vibe coding!

### Developer Documentation
- [Coding Standards](docs%2Fcoding-standards.md)
- [Exceptions](docs%2Fexceptions.md)
- [DI-Container](docs%2Fdi-container.md)
- [SQL Repository Usage](docs%2Fsql-repository-usage.md)
- [CLI.php - Command Line Interface](docs%2Fcli.md)
- [Api/Oauth2 - API and Oauth2](docs%2Foauth2.md)
- [User- Administration](docs%2Fuser-administration.md)

# Contributing
Contributions are highly encouraged. As the project is in early development, please note that code, features, and documentation are subject to change as we evolve toward a production-ready state.

# License
Garlic-Hub is open-source software licensed under the [Affero GPL v3.0 License](https://www.gnu.org/licenses/agpl-3.0.en.html).
