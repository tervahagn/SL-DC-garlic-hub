# Adding Modules to the Dependency Injection Container

This guide explains how to structure and add modules to the Dependency Injection (DI) container. Each module provides its own service definitions and is located in the `config/services` directory. Each service definition file must return an array of dependencies to be added to the DI container.

---

## Folder Structure

Your `config/services` directory should contain one PHP file for each module, with each file returning an array of service definitions. For example:

config/
└── services/
├──── _default.php
├──── auth.php
├──── player.php
├──── playlists.php
├──── templates.php
└──── mediapool.php


## Service Definition Example

Each service definition file should return an array of dependencies. Example for `auth.php`:

```php
use Psr\Container\ContainerInterface;

$dependencies = [];

$dependencies[LoginController::class] = DI\factory(function (ContainerInterface $container) {
    return new LoginController($container->get(UserMain::class));
});

return $dependencies;
```

## Loading All Modules
The `bootstrap.php`  dynamically load all service files recursively from config/services and add them to the DI container:

## Best Practices
1. **File Naming**
   Use the same names for your service files which you also use in `src/Modules` directory but in lowercase, e.g., auth.php, player.php, mediapool.php
   For default services, prefix the file name with _, e.g., _default.php.
2. **Modularity**
   Each module should define only the services it needs.
   Avoid hardcoding unrelated dependencies in a module's file.
3. **Lazy Loading**
   Use DI\factory() or DI\autowire() to define services, enabling the DI container to lazily load dependencies.