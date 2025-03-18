# Pagination Utility Documentation

The Pagination utility provides an way to handle and manage paging of records in data grids. It generates structured links for pagination control and allows customization for elements displayed per page. This document describes the usage of the pagination formatter class implemented within the Garlic Hub application.

## Overview

The paginator consists of two classes that work together to create and preformat dynamic pagination elements efficiently. It supports generating navigational links for page numbers and dropdown menus for controlling the number of elements visible on one page.

1. **Builder** - Responsible for building pagination links
2 **Formater** - Preformat the  pagination links to suit for [datagrid.mustache](../../../templates/generic/datagrid.mustache) template.
2. 
3. **BaseFilterParameters** - abstract helper class which manages filter parameters including pagination-related ones

## Usage

### Initialization

You must set the site route and base filtering options to initialize the paginator formatter:

``` php
use App\Framework\Utils\DataGrid\Paginator\Formatter;
use App\Framework\Utils\FormParameters\BaseFilterParameters;

// Assume $baseFilter is already created and configured as required.
$formatter = new Formatter();

$formatter->setBaseFilter($baseFilter)
          ->setSite('your-site-route');
```
Here:
- **`setBaseFilter(BaseFilterParameters)`**: Accepts filtering and sorting parameters for pagination.
- **`setSite(string)`**: Defines the base URL or route of your site pagination links.

## API Methods
### formatLinks(array $pageLinks): array
Generates pagination hyperlinks based on provided page information.
**Parameters:**
- `$pageLinks`: An array containing pages information (`['name' => 'displayName', 'page' => pageNumber]`).

**Returned Properties:**
- `ELEMENTS_PAGELINK`: Generated URL for each page with pagination and sorting parameters applied.
- `ELEMENTS_PAGENAME`: Displayed link name for the page.
- `ELEMENTS_PAGENUMBER`: Numeric representation of the page's position.

**Example:**
``` php
$pageLinks = [
    ['name' => 'Previous', 'page' => 1],
    ['name' => 'Next', 'page' => 3],
];

$formattedLinks = $formatter->formatLinks($pageLinks);
```
**Example output:**
``` php
[
    [
        'ELEMENTS_PAGELINK' => '/your-site-route?elements_page=1&sort_column=name&sort_order=asc&elements_per_page=10',
        'ELEMENTS_PAGENAME' => 'Previous',
        'ELEMENTS_PAGENUMBER' => 1
    ],
    [
        'ELEMENTS_PAGELINK' => '/your-site-route?elements_page=3&sort_column=name&sort_order=asc&elements_per_page=10',
        'ELEMENTS_PAGENAME' => 'Next',
        'ELEMENTS_PAGENUMBER' => 3
    ],
]
```
### formatDropdown(array $dropDownSettings): array
Constructs dropdown link data to enable users to select the number of visible elements per page.
**Parameters:**
- `$dropDownSettings`: Array with the following keys:
    - `min`: Minimum number of elements per page selectable.
    - `max`: Maximum number of elements per page selectable.
    - `steps`: Increment steps value for elements per page selection.

**Returned Properties:**
- `ELEMENTS_PER_PAGE_VALUE`: Numerical value representing selectable elements count.
- `ELEMENTS_PER_PAGE_DATA_LINK`: URL linkage adjusting the elements per page.
- `ELEMENTS_PER_PAGE_NAME`: Display value of selectable options.
- `ELEMENTS_PER_PAGE_SELECTED`: Indicates selected value (`'selected'`) if the current selection matches.

**Example:**
``` php
$dropDownSettings = ['min' => 10, 'max' => 50, 'steps' => 10];

$formattedDropdown = $formatter->formatDropdown($dropDownSettings);
```
**Example output:**
``` php
[
    [
        'ELEMENTS_PER_PAGE_VALUE' => 10,
        'ELEMENTS_PER_PAGE_DATA_LINK' => '/your-site-route?elements_per_page=10&sort_column=name&sort_order=asc&elements_page=1',
        'ELEMENTS_PER_PAGE_NAME' => 10,
        'ELEMENTS_PER_PAGE_SELECTED' => ''
    ],
    [
        'ELEMENTS_PER_PAGE_VALUE' => 20,
        'ELEMENTS_PER_PAGE_DATA_LINK' => '/your-site-route?elements_per_page=20&sort_column=name&sort_order=asc&elements_page=1',
        'ELEMENTS_PER_PAGE_NAME' => 20,
        'ELEMENTS_PER_PAGE_SELECTED' => 'selected'
    ],
    // more entries ...
]
```
## Exception Handling
Both API methods mentioned (`formatLinks()` and `formatDropdown()`) can throw a `ModuleException`. Users of this formatter class should handle these exceptions to gracefully manage possible configuration errors.

## Best Practices

1. Always set the base filter parameters before calling `create()`
2. Consider using shortened pagination for large data sets
3. Include "items per page" dropdown for better user experience
4. Handle exceptions properly to prevent application errors

## Limitations
- The paginator assumes a 1-indexed page numbering system
- The shortened pagination algorithm may not be suitable for all use cases
- Custom styling of pagination elements must be handled at the template level