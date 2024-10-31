# Coding Standards

This document outlines the coding standards and best practices for gralic-hub. Following these conventions ensures that our code remains clean, consistent, and easy to read across the entire codebase.

---

## General Guidelines

- Follow **PSR-1** and **PSR-12** standards for coding style.
- Use clear, descriptive names for all classes, methods, and variables.
- Keep code modular, with single-responsibility classes and functions.
- Ensure all code is properly documented with `phpdoc` comments.

---

## 1. Class Names

- **Style**: `PascalCase`
- **Convention**: Class names should begin with an uppercase letter, with each subsequent word also capitalized.
- **Examples**:
    - `UserService`
    - `OrderProcessor`
    - `LocaleSubscriber`

## 2. Method Names

- **Style**: `camelCase`
- **Convention**: Method names should start with a lowercase letter, with each new word capitalized.
- **Examples**:
    - `getUser()`
    - `processOrder()`
    - `setDefaultLocale()`

## 3. Variables

- **Style**: `camelCase` for most variables, `snake_case` only when interacting with database fields or when clarity requires it.
- **Convention**:
    - Use meaningful names that indicate the purpose of the variable.
    - Constants should be in `UPPER_CASE`.
- **Examples**:
    - `$userList`, `$itemCount`, `$defaultLocale` (standard variables)
    - `$user_id`, `$order_total` (when dealing with database-specific data)
    - `MAX_USER_COUNT`, `DEFAULT_LOCALE` (constants)

## 4. Constants

- **Style**: `UPPER_CASE`
- **Convention**: Use uppercase letters with underscores separating words.
- **Examples**:
    - `MAX_ITEMS`
    - `API_BASE_URL`
    - `DEFAULT_TIMEOUT`

## 5. File Names

- **Style**: `PascalCase`
- **Convention**: File names should match class names exactly and use PascalCase.
- **Examples**:
    - `UserService.php`
    - `OrderProcessor.php`

## 6. Function and Parameter Documentation

All functions and methods should be documented with `phpdoc` comments. These comments should include:

- **Description** of the function's purpose.
- **Parameters**: Each parameter should be described with its expected data type and purpose.
- **Return Type**: Describe the return type of the function.

### Example

```php
/**
 * Processes a user's order and returns a confirmation number.
 *
 * @param int $userId The ID of the user.
 * @param array $orderDetails An array of order details.
 * @return string Confirmation number of the processed order.
 */
public function processOrder(int $userId, array $orderDetails): string
{
    // Method implementation
}

```

## 7. Code Formatting

- **Indentation**: Use 4 spaces per indentation level.
- **Braces**: Place opening braces `{` on a new line, directly below the control statement or function definition.
- **Line Length**: Limit lines to 80-120 characters where possible to enhance readability.
- **Spacing**:
    - Add a single blank line between methods to improve readability.
    - Add spaces around operators (e.g., `=`, `+`, `-`, `==`).

### Example

```php
public function processOrder(int $userId, array $orderDetails)
{
    if ($userId > 0)
    {
        // Process the order
    }
    else
    {
        // Handle invalid user ID
    }
}
```

## 8. Error Handling and Exception Management

- Use meaningful exception messages to provide clear context about the error.
- Avoid using generic exceptions (e.g., `Exception`) when specific exceptions are available, as they improve debugging and readability.
- Log errors appropriately, but avoid exposing sensitive information in error messages to maintain security.
- Wrap potentially error-prone code in try-catch blocks where necessary to handle exceptions gracefully and ensure application stability.

### Example

```php
try
{
    $this->processOrder($orderId);
}
catch (OrderNotFoundException $e)
{
    // Log the error and display a user-friendly message
    $logger->error("Order not found: " . $e->getMessage());
    echo "The order could not be found. Please check the ID and try again.";
}
catch (Exception $e)
{
    // Generic catch for unexpected exceptions
    $logger->error("Unexpected error: " . $e->getMessage());
    echo "An unexpected error occurred. Please try again later.";
}
```

## 9. Naming Conventions for Interfaces and Abstract Classes

- **Interfaces** should end with `Interface` (e.g., `UserRepositoryInterface`).
- **Abstract Classes** should start with `Abstract` (e.g., `AbstractOrderProcessor`).

## 10. Commenting Style and Inline Documentation

- **Inline Comments**: Use inline comments sparingly. Only comment on complex logic or decisions that are not immediately clear.
- Keep comments up-to-date with any code changes to ensure accuracy.

## 12. Testing Standards

- Write unit tests for all public methods to validate functionality.
- Use descriptive names for test methods (e.g., `testProcessOrderReturnsConfirmationNumber`) for clarity.
- Ensure tests are isolated and avoid dependencies on external resources (e.g., database, filesystem) for reliable results.

## 13. Coding Style Check:

- Run `phpcs` to check coding standards compliance and `phpcbf` to auto-fix violations where possible.
