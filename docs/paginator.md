# Paginator 

## Overview

The Paginator is a comprehensive solution for handling pagination in web applications. It consists of several components that work together to create, manage, and render pagination elements efficiently. The main components include:

1. **PaginatorService** - The main entry point for pagination functionality
2. **Creator** - Responsible for building pagination links
3. **Renderer** - Handles the rendering of pagination links in a format suitable for templates
4. **BaseFilterParameters** - Manages filter parameters including pagination-related ones

## PaginatorService

This is the main service that orchestrates pagination functionality.

### Initialization

```php
// Create the PaginatorService via dependency injection
$paginatorService = new PaginatorService($creator, $renderer);

// Set the base filter parameters
$paginatorService->setBaseFilter($baseFilterParameters);
```

### Methods

#### `setBaseFilter(BaseFilterParameters $baseFilter): PaginatorService`

Sets the filter parameters used for pagination.

```php
$paginatorService->setBaseFilter($baseFilterParameters);
```

#### `create(int $totalItems, bool $usePager = false, bool $shortened = true): void`

Creates pagination links based on the total number of items.

Parameters:
- `$totalItems`: The total number of items to paginate
- `$usePager`: Whether to include "first", "previous", "next", "last" links
- `$shortened`: Whether to shorten pagination by showing only relevant page numbers

```php
// Create pagination for 100 items with pager navigation and shortened display
$paginatorService->create(100, true, true);
```

#### `renderPagination(string $site): array`

Renders the pagination links for a specific site/page.

Parameters:
- `$site`: The site/page name for which the pagination is being rendered

Returns:
- Array of pagination links with the following structure:
    - `ELEMENTS_PAGELINK`: The URL for the page
    - `ELEMENTS_PAGENAME`: The display name for the link
    - `ELEMENTS_PAGENUMBER`: The page number

```php
// Render pagination for the "users" page
$paginationLinks = $paginatorService->renderPagination('users');
```

#### `renderElementsPerSiteDropDown(int $min = 10, int $max = 100, int $steps = 10): array`

Renders a dropdown for selecting the number of items to display per page.

Parameters:
- `$min`: The minimum number of items per page (default: 10)
- `$max`: The maximum number of items per page (default: 100)
- `$steps`: The step size between options (default: 10)

Returns:
- Array of options with the following structure:
    - `ELEMENTS_PER_PAGE_VALUE`: The numeric value
    - `ELEMENTS_PER_PAGE_NAME`: The display name
    - `ELEMENTS_PER_PAGE_SELECTED`: Whether this option is currently selected

```php
// Render a dropdown with options from 10 to 50 in steps of 5
$dropdownOptions = $paginatorService->renderElementsPerSiteDropDown(10, 50, 5);
```

## Creator Component

The Creator component builds pagination links based on the provided parameters.

### Key Methods

#### `init(BaseFilterParameters $baseFilter, int $totalItems, bool $usePager = false, bool $shortened = true): static`

Initializes the creator with the necessary parameters.

#### `buildPagerLinks(): static`

Builds the pagination links according to the current state.

#### `getPagerLinks(): array`

Returns the built pagination links.

## Renderer Component

The Renderer component formats pagination links for template consumption.

### Key Methods

#### `render(array $pageLinks, string $site, BaseFilterParameters $baseFilter): array`

Renders pagination links for use in templates.

## BaseFilterParameters

This component manages filter parameters used for pagination and sorting:

- `elements_per_page`: Number of items per page
- `elements_page`: Current page number
- `sort_column`: Column name for sorting
- `sort_order`: Sort order (ASC/DESC)

## Usage Example

```php
// Assume dependency injection for $creator, $renderer, and $baseFilter
$paginatorService = new PaginatorService($creator, $renderer);
$paginatorService->setBaseFilter($baseFilter);

// Create pagination for 150 total items
$paginatorService->create(150, true, true);

// Render the pagination for the "products" page
$paginationLinks = $paginatorService->renderPagination('products');

// Get a dropdown for selecting items per page
$itemsPerPageDropdown = $paginatorService->renderElementsPerSiteDropDown(10, 50, 10);

// Use in a template
// foreach($paginationLinks as $link) { ... }
// foreach($itemsPerPageDropdown as $option) { ... }
```

## Error Handling

The Paginator components may throw `ModuleException` in case of errors. It's recommended to handle these exceptions appropriately in your application code.

```php
try {
    $paginationLinks = $paginatorService->renderPagination('products');
} catch (ModuleException $e) {
    // Handle exception
    $logger->error('Pagination error: ' . $e->getMessage());
}
```

## Best Practices

1. Always set the base filter parameters before calling `create()`
2. Consider using shortened pagination for large data sets
3. Include "items per page" dropdown for better user experience
4. Handle exceptions properly to prevent application errors

## Limitations

- The paginator assumes a 1-indexed page numbering system
- The shortened pagination algorithm may not be suitable for all use cases
- Custom styling of pagination elements must be handled at the template level