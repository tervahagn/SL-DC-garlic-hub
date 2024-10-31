# Custom Exception Classes Documentation

This document outlines the purpose and usage of the custom Exception classes in the application. These classes extend `BaseException`, which provides a flexible structure for managing errors across various modules in the framework. Each exception class represents a specific category of errors, allowing for targeted handling and logging.

---

## 1. `BaseException`

**Namespace**: `App\Framework\Exceptions`

### Overview

`BaseException` is the base class for all custom exceptions in the application. It extends the standard PHP `Exception` class with additional support for:
- Module-specific naming to identify the source module of the error.
- Methods to retrieve exception details as an array or formatted string.

### Methods

- **`setModuleName(string $module_name): self`**  
  Sets the module name where the exception originated.

- **`getModuleName(): string`**  
  Returns the name of the module.

- **`getDetails(): array`**  
  Returns an associative array with details of the exception, including the module name, message, code, file, line, and stack trace.

- **`getDetailsAsString(): string`**  
  Returns a formatted string with the exception details for easier readability in logs.

---

## 2. `CoreException`

**Namespace**: `App\Framework\Exceptions`

### Overview

`CoreException` is used to handle errors related to the core system functions. This exception sets the module name automatically to "Core".

### Constructor

- **`__construct(string $message, int $code = 0, \Exception $previous = null)`**  
  Initializes the exception with a message, code, and optional previous exception, setting the module name to "Core".

---

## 3. `DatabaseException`

**Namespace**: `App\Framework\Exceptions`

### Overview

`DatabaseException` is designed for handling database-related errors. It automatically sets the module name to "DB".

### Constructor

- **`__construct(string $message, int $code = 0, \Exception $previous = null)`**  
  Initializes the exception with a message, code, and optional previous exception, setting the module name to "DB".

---

## 4. `FrameworkException`

**Namespace**: `App\Framework\Exceptions`

### Overview

`FrameworkException` handles errors related to the framework's internal operations. This exception sets the module name to "Framework" by default.

### Constructor

- **`__construct(string $message, int $code = 0, \Exception $previous = null)`**  
  Initializes the exception with a message, code, and optional previous exception, setting the module name to "Framework".

---

## 5. `ModuleException`

**Namespace**: `App\Framework\Exceptions`

### Overview

`ModuleException` is a flexible exception class that can handle errors in specific modules, with a module name dynamically provided at runtime.

### Constructor

- **`__construct(string $module_name, string $message = '', int $code = 0, \Exception $previous = null)`**  
  Initializes the exception with a module name, message, code, and optional previous exception.

### Usage

This class is useful for errors in modules that do not have a dedicated exception class but still require a unique module name for identification.

---

## 6. `UserException`

**Namespace**: `App\Framework\Exceptions`

### Overview

`UserException` handles errors specific to user-related operations. This exception sets the module name automatically to "User".

### Constructor

- **`__construct(string $message, int $code = 0, \Exception $previous = null)`**  
  Initializes the exception with a message, code, and optional previous exception, setting the module name to "User".

---

## Usage Guidelines

1. **Logging and Monitoring**: Each exception class provides detailed information that can be logged or used in error monitoring systems. For consistent logging, retrieve details with `getDetails()` or `getDetailsAsString()` for formatted output.

2. **Module Identification**: By setting the module name, each exception can be traced back to its source module, making debugging and maintenance easier.

3. **Exception Handling**: Use these custom exceptions to separate error handling logic by module type. This approach enhances readability and organization in error management.

---

## Example Usage

```php
use App\Framework\Exceptions\DatabaseException;

try
 {
    // Some database operation
    throw new DatabaseException('Database connection failed', 500);
}
catch (DatabaseException $e)
 {
    // Log or handle the specific exception
    echo $e->getDetailsAsString();
}
