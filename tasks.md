# UI Transformation Tasks: Garlic Hub → Enplug-Style Interface

## Overview
Transform the current Garlic Hub UI from its functional design to a modern, Enplug-style digital signage management interface with enhanced UX patterns.

## Phase 1: Foundation & Design System (Days 1-3)

### Task 1.1: Update CSS Custom Properties
**File:** `public/css/theme-base.css`

**Current:**
```css
:root {
    --gh-primary-color: #03393a;
    --gh-secondary-color: rgb(0, 122, 80);
    --gh-third-color: rgba(0, 122, 80, 0.32);
    --gh-primary-text-color: #000;
    --gh-light-color: #f2f3f6;
    --gh-dark-color: #000;
}
```

**New Enplug-Style:**
```css
:root {
    /* Primary Colors - Modern Blue/Teal Palette */
    --gh-primary-color: #1e40af;
    --gh-secondary-color: #0ea5e9;
    --gh-accent-color: #06b6d4;
    --gh-success-color: #10b981;
    --gh-warning-color: #f59e0b;
    --gh-danger-color: #ef4444;
    
    /* Neutral Colors */
    --gh-text-primary: #1f2937;
    --gh-text-secondary: #6b7280;
    --gh-text-muted: #9ca3af;
    --gh-border-color: #e5e7eb;
    --gh-border-light: #f3f4f6;
    
    /* Background Colors */
    --gh-bg-primary: #ffffff;
    --gh-bg-secondary: #f9fafb;
    --gh-bg-tertiary: #f3f4f6;
    --gh-bg-card: #ffffff;
    
    /* Interactive Colors */
    --gh-link-color: #1e40af;
    --gh-link-hover: #1d4ed8;
    --gh-button-primary: #1e40af;
    --gh-button-hover: #1d4ed8;
    
    /* Shadows & Effects */
    --gh-shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --gh-shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    --gh-shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    --gh-shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
    
    /* Spacing */
    --gh-spacing-xs: 0.25rem;
    --gh-spacing-sm: 0.5rem;
    --gh-spacing-md: 1rem;
    --gh-spacing-lg: 1.5rem;
    --gh-spacing-xl: 2rem;
    --gh-spacing-2xl: 3rem;
    
    /* Border Radius */
    --gh-radius-sm: 0.375rem;
    --gh-radius-md: 0.5rem;
    --gh-radius-lg: 0.75rem;
    --gh-radius-xl: 1rem;
    
    /* Transitions */
    --gh-transition-fast: 150ms ease-in-out;
    --gh-transition-normal: 250ms ease-in-out;
    --gh-transition-slow: 350ms ease-in-out;
}
```

### Task 1.2: Modernize Typography System
**File:** `public/css/theme-base.css`

**Add new font imports:**
```css
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

:root {
    --gh-font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    --gh-font-size-xs: 0.75rem;
    --gh-font-size-sm: 0.875rem;
    --gh-font-size-base: 1rem;
    --gh-font-size-lg: 1.125rem;
    --gh-font-size-xl: 1.25rem;
    --gh-font-size-2xl: 1.5rem;
    --gh-font-size-3xl: 1.875rem;
    
    --gh-font-weight-light: 300;
    --gh-font-weight-normal: 400;
    --gh-font-weight-medium: 500;
    --gh-font-weight-semibold: 600;
    --gh-font-weight-bold: 700;
    
    --gh-line-height-tight: 1.25;
    --gh-line-height-normal: 1.5;
    --gh-line-height-relaxed: 1.75;
}
```

**Update base typography:**
```css
html {
    font-family: var(--gh-font-family);
    font-size: var(--gh-font-size-base);
    line-height: var(--gh-line-height-normal);
    color: var(--gh-text-primary);
}

body {
    background-color: var(--gh-bg-secondary);
    color: var(--gh-text-primary);
}

h1, h2, h3, h4, h5, h6 {
    font-family: var(--gh-font-family);
    font-weight: var(--gh-font-weight-semibold);
    line-height: var(--gh-line-height-tight);
    color: var(--gh-text-primary);
}

h1 { font-size: var(--gh-font-size-3xl); margin: 0 0 var(--gh-spacing-lg) 0; }
h2 { font-size: var(--gh-font-size-2xl); margin: 0 0 var(--gh-spacing-md) 0; }
h3 { font-size: var(--gh-font-size-xl); margin: 0 0 var(--gh-spacing-sm) 0; }
```

### Task 1.3: Create Modern Card Component System
**File:** `public/css/theme-base.css`

**Add card components:**
```css
/* Card System */
.card {
    background: var(--gh-bg-card);
    border: 1px solid var(--gh-border-color);
    border-radius: var(--gh-radius-lg);
    box-shadow: var(--gh-shadow-sm);
    transition: all var(--gh-transition-normal);
    overflow: hidden;
}

.card:hover {
    box-shadow: var(--gh-shadow-md);
    transform: translateY(-1px);
}

.card-header {
    padding: var(--gh-spacing-lg);
    border-bottom: 1px solid var(--gh-border-light);
    background: var(--gh-bg-secondary);
}

.card-body {
    padding: var(--gh-spacing-lg);
}

.card-footer {
    padding: var(--gh-spacing-lg);
    border-top: 1px solid var(--gh-border-light);
    background: var(--gh-bg-secondary);
}

/* Enhanced Main Element Groups */
.main-element-group {
    background: var(--gh-bg-card);
    border: 1px solid var(--gh-border-color);
    border-radius: var(--gh-radius-lg);
    box-shadow: var(--gh-shadow-sm);
    padding: var(--gh-spacing-lg);
    margin: var(--gh-spacing-md) 0;
    transition: all var(--gh-transition-normal);
}

.main-element-group:hover {
    box-shadow: var(--gh-shadow-md);
}
```

## Phase 2: Navigation & Header Redesign (Days 4-5)

### Task 2.1: Modernize Header Navigation
**File:** `public/css/theme-base.css`

**Replace current header styles:**
```css
/* Modern Header */
header {
    background: var(--gh-bg-card);
    border-bottom: 1px solid var(--gh-border-color);
    box-shadow: var(--gh-shadow-sm);
    position: sticky;
    top: 0;
    z-index: 1000;
}

header nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--gh-spacing-md) var(--gh-spacing-xl);
    max-width: 1400px;
    margin: 0 auto;
}

/* Logo Styling */
header nav .logo {
    display: flex;
    align-items: center;
    gap: var(--gh-spacing-sm);
}

header nav .logo img {
    height: 40px;
    width: auto;
}

/* Navigation Menu */
header nav .menu {
    display: flex;
    gap: var(--gh-spacing-lg);
    align-items: center;
    list-style: none;
    margin: 0;
    padding: 0;
}

header nav .menu li {
    position: relative;
}

header nav .menu a {
    display: flex;
    align-items: center;
    gap: var(--gh-spacing-sm);
    padding: var(--gh-spacing-sm) var(--gh-spacing-md);
    color: var(--gh-text-secondary);
    text-decoration: none;
    border-radius: var(--gh-radius-md);
    transition: all var(--gh-transition-fast);
    font-weight: var(--gh-font-weight-medium);
}

header nav .menu a:hover {
    color: var(--gh-text-primary);
    background: var(--gh-bg-tertiary);
}

/* Dropdown Menus */
header nav .menu ul {
    position: absolute;
    top: 100%;
    left: 0;
    background: var(--gh-bg-card);
    border: 1px solid var(--gh-border-color);
    border-radius: var(--gh-radius-lg);
    box-shadow: var(--gh-shadow-xl);
    min-width: 200px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all var(--gh-transition-normal);
    z-index: 1001;
}

header nav .menu li:hover ul {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

header nav .menu ul li a {
    padding: var(--gh-spacing-sm) var(--gh-spacing-lg);
    border-radius: 0;
    border-bottom: 1px solid var(--gh-border-light);
}

header nav .menu ul li:last-child a {
    border-bottom: none;
}
```

### Task 2.2: Update Header Template
**File:** `templates/layouts/main_layout.mustache`

**Replace header section:**
```html
<header>
    <nav>
        <div class="logo">
            <a href="/">
                <img src="/images/logo.svg" alt="Garlic Hub" width="150">
            </a>
        </div>
        
        <ul class="menu main-menu">
            {{#main_menu}}
                <li>
                    <a href="{{URL}}" title="{{LANG_MENU_POINT}}">
                        <i class="bi bi-{{ICON_NAME}}"></i>
                        {{LANG_MENU_POINT}}
                    </a>
                </li>
            {{/main_menu}}
        </ul>
        
        <div class="header-actions">
            <ul class="menu" id="language_select">
                <li>
                    <a href="#" class="language-selector">
                        <i class="bi bi-globe"></i>
                        {{CURRENT_LOCALE_UPPER}}
                    </a>
                    <ul>
                        {{#language_select}}
                            <li>
                                <a href="/set-locales/{{LOCALE_LONG}}" title="{{LOCALE_SMALL}}">
                                    {{LANGUAGE_NAME}}
                                </a>
                            </li>
                        {{/language_select}}
                    </ul>
                </li>
            </ul>
            
            {{#user_menu}}
            <ul class="menu" id="user_menu">
                <li>
                    <a href="#" class="user-menu-trigger">
                        <i class="bi bi-person-circle"></i>
                        <span>{{USERNAME}}</span>
                        <i class="bi bi-chevron-down"></i>
                    </a>
                    <ul>
                        {{#has_user_access}}
                        <li>
                            <a href="{{{LINK_USER_ACCESS}}}">
                                <i class="bi bi-shield-check"></i>
                                {{LANG_USER_ACCESS}}
                            </a>
                        </li>
                        {{/has_user_access}}
                        <li>
                            <a href="/profile/settings">
                                <i class="bi bi-gear"></i>
                                {{LANG_MANAGE_ACCOUNT}}
                            </a>
                        </li>
                        <li>
                            <a href="/logout">
                                <i class="bi bi-box-arrow-right"></i>
                                {{LANG_LOGOUT}}
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
            {{/user_menu}}
        </div>
    </nav>
</header>
```

## Phase 3: Form & Input Modernization (Days 6-7)

### Task 3.1: Modernize Form Elements
**File:** `public/css/theme-base.css`

**Replace current form styles:**
```css
/* Modern Form System */
form {
    margin: 0 auto;
}

fieldset {
    border: 1px solid var(--gh-border-color);
    border-radius: var(--gh-radius-lg);
    background: var(--gh-bg-secondary);
    padding: var(--gh-spacing-lg);
    margin: var(--gh-spacing-md) 0;
}

fieldset legend {
    padding: 0 var(--gh-spacing-sm);
    font-weight: var(--gh-font-weight-semibold);
    color: var(--gh-text-primary);
    font-size: var(--gh-font-size-lg);
}

/* Form Labels */
form label {
    display: flex;
    flex-direction: column;
    gap: var(--gh-spacing-xs);
    margin: 0 0 var(--gh-spacing-sm) 0;
    font-weight: var(--gh-font-weight-medium);
    color: var(--gh-text-primary);
}

/* Form Inputs */
input:not([type='checkbox'], [type='radio']), 
textarea, 
select, 
button, 
a.button {
    border: 1px solid var(--gh-border-color);
    border-radius: var(--gh-radius-md);
    padding: var(--gh-spacing-sm) var(--gh-spacing-md);
    font-family: var(--gh-font-family);
    font-size: var(--gh-font-size-base);
    line-height: var(--gh-line-height-normal);
    transition: all var(--gh-transition-fast);
    background: var(--gh-bg-card);
    color: var(--gh-text-primary);
}

input:focus, 
textarea:focus, 
select:focus {
    outline: none;
    border-color: var(--gh-primary-color);
    box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
}

input:hover, 
textarea:hover, 
select:hover {
    border-color: var(--gh-text-muted);
}

/* Button System */
button, a.button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: var(--gh-spacing-sm);
    padding: var(--gh-spacing-sm) var(--gh-spacing-lg);
    border: 1px solid transparent;
    border-radius: var(--gh-radius-md);
    font-family: var(--gh-font-family);
    font-weight: var(--gh-font-weight-medium);
    font-size: var(--gh-font-size-base);
    text-decoration: none;
    cursor: pointer;
    transition: all var(--gh-transition-fast);
    line-height: 1;
}

/* Primary Button */
button.primary, a.button.primary {
    background: var(--gh-button-primary);
    color: white;
    border-color: var(--gh-button-primary);
}

button.primary:hover, a.button.primary:hover {
    background: var(--gh-button-hover);
    border-color: var(--gh-button-hover);
    transform: translateY(-1px);
    box-shadow: var(--gh-shadow-md);
}

/* Secondary Button */
button.secondary, a.button.secondary {
    background: transparent;
    color: var(--gh-text-primary);
    border-color: var(--gh-border-color);
}

button.secondary:hover, a.button.secondary:hover {
    background: var(--gh-bg-tertiary);
    border-color: var(--gh-text-muted);
}

/* Button Sizes */
button.small, a.button.small {
    padding: var(--gh-spacing-xs) var(--gh-spacing-md);
    font-size: var(--gh-font-size-sm);
}

button.large, a.button.large {
    padding: var(--gh-spacing-md) var(--gh-spacing-xl);
    font-size: var(--gh-font-size-lg);
}
```

### Task 3.2: Enhanced Form Layout
**File:** `public/css/theme-base.css`

**Add form layout utilities:**
```css
/* Form Layout System */
.form-grid {
    display: grid;
    gap: var(--gh-spacing-lg);
}

.form-grid-2 {
    grid-template-columns: repeat(2, 1fr);
}

.form-grid-3 {
    grid-template-columns: repeat(3, 1fr);
}

.form-grid-4 {
    grid-template-columns: repeat(4, 1fr);
}

@media (max-width: 768px) {
    .form-grid-2,
    .form-grid-3,
    .form-grid-4 {
        grid-template-columns: 1fr;
    }
}

/* Field Wrapper */
.field-wrapper {
    margin: 0 0 var(--gh-spacing-lg) 0;
}

.field-wrapper:last-of-type {
    margin: 0;
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: var(--gh-spacing-md);
    justify-content: flex-end;
    align-items: center;
    padding: var(--gh-spacing-lg) 0;
    border-top: 1px solid var(--gh-border-light);
    margin-top: var(--gh-spacing-lg);
}

.form-actions.left {
    justify-content: flex-start;
}

.form-actions.center {
    justify-content: center;
}

.form-actions .spacer {
    margin-left: auto;
}
```

## Phase 4: Datatable & Grid Modernization (Days 8-9)

### Task 4.1: Modernize Datatable System
**File:** `public/css/theme-base.css`

**Replace current datatable styles:**
```css
/* Modern Datatable System */
.datatable {
    background: var(--gh-bg-card);
    border: 1px solid var(--gh-border-color);
    border-radius: var(--gh-radius-lg);
    overflow: hidden;
    box-shadow: var(--gh-shadow-sm);
}

/* Datatable Header */
.datatable-header {
    background: var(--gh-bg-secondary);
    border-bottom: 1px solid var(--gh-border-color);
    padding: var(--gh-spacing-lg);
}

.datatable-title {
    font-size: var(--gh-font-size-xl);
    font-weight: var(--gh-font-weight-semibold);
    color: var(--gh-text-primary);
    margin: 0;
}

/* Datatable Toolbar */
.datatable-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--gh-spacing-lg);
    border-bottom: 1px solid var(--gh-border-light);
    background: var(--gh-bg-secondary);
}

.datatable-filters {
    display: flex;
    gap: var(--gh-spacing-md);
    align-items: center;
}

.datatable-actions {
    display: flex;
    gap: var(--gh-spacing-sm);
    align-items: center;
}

/* Enhanced Results Listing */
.results-listing {
    list-style: none;
    padding: 0;
    margin: 0;
}

.results-header {
    background: var(--gh-bg-tertiary);
    border-bottom: 1px solid var(--gh-border-color);
    font-weight: var(--gh-font-weight-medium);
    color: var(--gh-text-secondary);
}

.results-header ul {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: var(--gh-spacing-md);
    padding: var(--gh-spacing-md) var(--gh-spacing-lg);
    margin: 0;
    list-style: none;
}

.results-body {
    border-bottom: 1px solid var(--gh-border-light);
    transition: background-color var(--gh-transition-fast);
}

.results-body:hover {
    background: var(--gh-bg-tertiary);
}

.results-body ul {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: var(--gh-spacing-md);
    padding: var(--gh-spacing-md) var(--gh-spacing-lg);
    margin: 0;
    list-style: none;
    align-items: center;
}

/* Datatable Pagination */
.datatable-pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--gh-spacing-lg);
    background: var(--gh-bg-secondary);
    border-top: 1px solid var(--gh-border-light);
}

.pagination-info {
    color: var(--gh-text-secondary);
    font-size: var(--gh-font-size-sm);
}

.pagination-controls {
    display: flex;
    gap: var(--gh-spacing-sm);
    align-items: center;
}

.pagination-controls a,
.pagination-controls button {
    padding: var(--gh-spacing-xs) var(--gh-spacing-sm);
    border: 1px solid var(--gh-border-color);
    border-radius: var(--gh-radius-md);
    background: var(--gh-bg-card);
    color: var(--gh-text-primary);
    text-decoration: none;
    transition: all var(--gh-transition-fast);
}

.pagination-controls a:hover,
.pagination-controls button:hover {
    background: var(--gh-primary-color);
    color: white;
    border-color: var(--gh-primary-color);
}

.pagination-controls .current {
    background: var(--gh-primary-color);
    color: white;
    border-color: var(--gh-primary-color);
}
```

### Task 4.2: Update Datatable Template
**File:** `templates/generic/datatable.mustache`

**Replace with modern structure:**
```html
<div class="datatable">
    <div class="datatable-header">
        <h1 class="datatable-title">{{{LANG_PAGE_HEADER}}}</h1>
        {{{INSERT_SUBMENU}}}
    </div>
    
    <div class="datatable-toolbar">
        <div class="datatable-filters">
            <form action="{{{FORM_ACTION}}}" method="get" id="form_elements_search">
                <input type="hidden" name="sort_column" id="sort_column" value="{{{SORT_COLUMN}}}">
                <input type="hidden" name="sort_order" id="sort_order" value="{{{SORT_ORDER}}}">
                <input type="hidden" name="elements_page" id="elements_page" value="{{{ELEMENTS_PAGE}}}">
                <input type="hidden" name="elements_per_page" id="elements_per_page" value="{{{ELEMENTS_PER_PAGE}}}">
                
                {{#element_hidden}}
                    {{{HIDDEN_HTML_ELEMENT}}}
                {{/element_hidden}}
                
                <div class="form-grid form-grid-3">
                    {{#form_element}}
                        <div class="field-wrapper">
                            <label for="{{{HTML_ELEMENT_ID}}}">{{{LANG_ELEMENT_LABEL}}}</label>
                            {{{HTML_ELEMENT}}}
                        </div>
                    {{/form_element}}
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="primary">
                        <i class="bi bi-search"></i>
                        Search
                    </button>
                    <button type="reset" class="secondary">
                        <i class="bi bi-arrow-clockwise"></i>
                        Reset
                    </button>
                </div>
            </form>
        </div>
        
        <div class="datatable-actions">
            {{#has_add}}
                <a href="{{{ADD_LINK}}}" class="button primary">
                    <i class="bi bi-plus"></i>
                    {{LANG_ADD_NEW}}
                </a>
            {{/has_add}}
            
            {{#elements_export_results}}
                <button class="button secondary" onclick="exportData()">
                    <i class="bi bi-download"></i>
                    Export
                </button>
            {{/elements_export_results}}
        </div>
    </div>
    
    <div class="datatable-content">
        <ul class="results-listing">
            <li>
                <ul class="results-header">
                    {{#elements_result_header}}
                    <li class="{{{CONTROL_NAME}}}">
                        {{#if_sortable}}
                        <a href="{{{LINK_CONTROL_SORT_ORDER}}}" class="header_sort_column" id="{{{SORT_CONTROL_NAME}}}_sort_column">
                            {{{LANG_CONTROL_NAME}}}
                            {{{SORTABLE_ORDER}}}
                        </a>
                        {{/if_sortable}}
                        {{^if_sortable}}
                            {{{LANG_CONTROL_NAME_2}}}
                        {{/if_sortable}}
                    </li>
                    {{/elements_result_header}}
                    <li class="actions">Actions</li>
                </ul>
            </li>
            
            {{#elements_results}}
            <li>
                <ul class="results-body" data-id="{{{UNIT_ID}}}" style="{{{MARKED_AS_DISABLED}}}">
                    {{#elements_result_element}}
                    <li class="{{{CONTROL_NAME_BODY}}}">
                        {{#is_link}}
                            <a href="{{{CONTROL_ELEMENT_VALUE_LINK}}}" id="{{{CONTROL_ELEMENT_VALUE_ID}}}" class="{{{CONTROL_ELEMENT_VALUE_CLASS}}}" title="{{{CONTROL_ELEMENT_VALUE_TITLE}}">
                                {{{CONTROL_ELEMENT_VALUE_NAME}}}
                            </a>
                            {{{CONTROL_ELEMENT_ADDITIONAL_TEXT}}}
                        {{/is_link}}

                        {{#is_span}}
                            <span id="{{{CONTROL_ELEMENT_VALUE_ID}}}" class="{{{CONTROL_ELEMENT_VALUE_CLASS}}}" title="{{{CONTROL_ELEMENT_VALUE_TITLE}}">
                                {{{CONTROL_ELEMENT_VALUE_NAME}}}
                            </span>
                        {{/is_span}}

                        {{#is_text}}
                            {{{CONTROL_ELEMENT_VALUE_TEXT}}}
                        {{/is_text}}

                        {{#is_button}}
                            <button data-id="{{{CONTROL_BUTTON_ID}}}" class="button secondary small {{{CONTROL_BUTTON_CLASS}}}" title="{{{CONTROL_BUTTON_TITLE}}">
                                {{{CONTROL_BUTTON_VALUE}}}
                            </button>
                        {{/is_button}}

                        {{#is_icon}}
                            <i class="{{{ICON_CLASS}}}" title="{{{ICON_TITLE}}}"></i>
                        {{/is_icon}}

                        {{#is_checkbox}}
                            <label class="checkbox-wrapper">
                                <input type="checkbox" {{{SELECT_DISABLED}}} title="{{{LANG_CHECKBOX_NAME}}}">
                                <span class="checkmark"></span>
                            </label>
                        {{/is_checkbox}}
                    </li>
                    {{/elements_result_element}}
                    
                    <li class="actions">
                        <div class="action-buttons">
                            {{#action_buttons}}
                                <button class="button secondary small" title="{{{BUTTON_TITLE}}}" onclick="{{{ONCLICK_ACTION}}}">
                                    <i class="{{{ICON_CLASS}}"></i>
                                </button>
                            {{/action_buttons}}
                        </div>
                    </li>
                </ul>
            </li>
            {{/elements_results}}
        </ul>
    </div>
    
    <div class="datatable-pagination">
        <div class="pagination-info">
            Showing {{#elements_results}}{{/elements_results}} of {{results_count}} results
        </div>
        
        <div class="pagination-controls">
            {{#elements_pager}}
                <a href="{{{PAGE_LINK}}}" class="{{{PAGE_CLASS}}">{{{PAGE_NUMBER}}}</a>
            {{/elements_pager}}
        </div>
    </div>
</div>
```

## Phase 5: Dashboard & Widget System (Days 10-11)

### Task 5.1: Modernize Dashboard Layout
**File:** `public/css/theme-base.css`

**Replace current dashboard styles:**
```css
/* Modern Dashboard System */
.dashboards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: var(--gh-spacing-lg);
    padding: 0;
    margin: 0;
}

.dashboard {
    background: var(--gh-bg-card);
    border: 1px solid var(--gh-border-color);
    border-radius: var(--gh-radius-lg);
    box-shadow: var(--gh-shadow-sm);
    overflow: hidden;
    transition: all var(--gh-transition-normal);
}

.dashboard:hover {
    box-shadow: var(--gh-shadow-md);
    transform: translateY(-2px);
}

.dashboard-header {
    padding: var(--gh-spacing-lg);
    border-bottom: 1px solid var(--gh-border-light);
    background: var(--gh-bg-secondary);
}

.dashboard h2 {
    margin: 0;
    font-size: var(--gh-font-size-lg);
    font-weight: var(--gh-font-weight-semibold);
    color: var(--gh-text-primary);
}

.dashboard-body {
    padding: var(--gh-spacing-lg);
}

.dashboard ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.dashboard ul li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--gh-spacing-sm) 0;
    border-bottom: 1px solid var(--gh-border-light);
}

.dashboard ul li:last-child {
    border-bottom: none;
}

.dashboard ul li strong {
    font-weight: var(--gh-font-weight-medium);
    color: var(--gh-text-primary);
}

.dashboard ul li span {
    color: var(--gh-text-secondary);
    font-size: var(--gh-font-size-sm);
}

/* Dashboard Stats */
.dashboard-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: var(--gh-spacing-md);
    margin-top: var(--gh-spacing-md);
}

.stat-item {
    text-align: center;
    padding: var(--gh-spacing-md);
    background: var(--gh-bg-tertiary);
    border-radius: var(--gh-radius-md);
}

.stat-value {
    font-size: var(--gh-font-size-2xl);
    font-weight: var(--gh-font-weight-bold);
    color: var(--gh-primary-color);
    line-height: 1;
}

.stat-label {
    font-size: var(--gh-font-size-sm);
    color: var(--gh-text-secondary);
    margin-top: var(--gh-spacing-xs);
}
```

### Task 5.2: Update Dashboard Template
**File:** `templates/home.mustache`

**Replace with modern structure:**
```html
<div class="page-header">
    <h1>{{{LANG_PAGE_HEADER}}}</h1>
    <p class="page-subtitle">Welcome to your digital signage management dashboard</p>
</div>

<article class="dashboards">
    {{#dashboard}}
    <section class="dashboard">
        <div class="dashboard-header">
            <h2>{{{LANG_DASHBOARD_TITLE}}}</h2>
        </div>
        <div class="dashboard-body">
            {{{DASHBOARD_CONTENT}}}
            
            {{#dashboard_stats}}
            <div class="dashboard-stats">
                {{#stats}}
                <div class="stat-item">
                    <div class="stat-value">{{{VALUE}}}</div>
                    <div class="stat-label">{{{LABEL}}}</div>
                </div>
                {{/stats}}
            </div>
            {{/dashboard_stats}}
        </div>
    </section>
    {{/dashboard}}
</article>
```

## Phase 6: Media & Playlist Interface (Days 12-13)

### Task 6.1: Modernize Media Grid
**File:** `public/css/mediapool/overview.css`

**Replace with modern grid system:**
```css
/* Modern Media Grid */
main {
    display: grid;
    grid-template-columns: 280px 1fr;
    grid-template-rows: auto 1fr;
    grid-template-areas:
        "sidebar header"
        "sidebar content";
    gap: var(--gh-spacing-lg);
    height: calc(100vh - 80px);
    padding: var(--gh-spacing-lg);
}

/* Sidebar */
aside {
    grid-area: sidebar;
    background: var(--gh-bg-card);
    border: 1px solid var(--gh-border-color);
    border-radius: var(--gh-radius-lg);
    padding: var(--gh-spacing-lg);
    overflow-y: auto;
}

.sidebar-header {
    margin-bottom: var(--gh-spacing-lg);
    padding-bottom: var(--gh-spacing-md);
    border-bottom: 1px solid var(--gh-border-light);
}

.sidebar-title {
    font-size: var(--gh-font-size-lg);
    font-weight: var(--gh-font-weight-semibold);
    color: var(--gh-text-primary);
    margin: 0 0 var(--gh-spacing-sm) 0;
}

/* Tree Filter */
#wrap_tree_filter {
    display: flex;
    gap: var(--gh-spacing-sm);
    align-items: center;
    margin-bottom: var(--gh-spacing-md);
}

#wrap_tree_filter > input {
    flex: 1;
    padding: var(--gh-spacing-sm);
    border: 1px solid var(--gh-border-color);
    border-radius: var(--gh-radius-md);
    font-size: var(--gh-font-size-sm);
}

/* Content Area */
section.content {
    grid-area: content;
    display: flex;
    flex-direction: column;
    gap: var(--gh-spacing-lg);
    overflow-y: auto;
}

.content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--gh-spacing-lg);
    background: var(--gh-bg-card);
    border: 1px solid var(--gh-border-color);
    border-radius: var(--gh-radius-lg);
}

.current-path {
    font-weight: var(--gh-font-weight-medium);
    color: var(--gh-text-primary);
}

.upload-actions {
    display: flex;
    gap: var(--gh-spacing-sm);
}

/* Media Grid */
.media-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: var(--gh-spacing-md);
    padding: var(--gh-spacing-lg);
    background: var(--gh-bg-card);
    border: 1px solid var(--gh-border-color);
    border-radius: var(--gh-radius-lg);
}

.media-item {
    background: var(--gh-bg-secondary);
    border: 1px solid var(--gh-border-light);
    border-radius: var(--gh-radius-md);
    padding: var(--gh-spacing-md);
    text-align: center;
    transition: all var(--gh-transition-fast);
    cursor: pointer;
}

.media-item:hover {
    border-color: var(--gh-primary-color);
    box-shadow: var(--gh-shadow-md);
    transform: translateY(-2px);
}

.media-item img {
    width: 100%;
    height: 120px;
    object-fit: cover;
    border-radius: var(--gh-radius-sm);
    margin-bottom: var(--gh-spacing-sm);
}

.media-item .media-name {
    font-size: var(--gh-font-size-sm);
    font-weight: var(--gh-font-weight-medium);
    color: var(--gh-text-primary);
    margin-bottom: var(--gh-spacing-xs);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.media-item .media-info {
    font-size: var(--gh-font-size-xs);
    color: var(--gh-text-secondary);
}
```

### Task 6.2: Update Media Template
**File:** `templates/mediapool/overview.mustache`

**Replace with modern structure:**
```html
<main>
    <aside>
        <div class="sidebar-header">
            <h3 class="sidebar-title">Media Library</h3>
            <div id="wrap_tree_filter">
                <input type="text" placeholder="Search folders..." id="tree_filter">
                <i class="bi bi-search"></i>
            </div>
        </div>
        
        <div class="wunderbaum" id="media_tree"></div>
    </aside>
    
    <section class="content">
        <div class="content-header">
            <div class="current-path">
                <i class="bi bi-folder"></i>
                <span id="currentPath">{{{CURRENT_PATH}}}</span>
            </div>
            
            <div class="upload-actions">
                <button id="openUploadDialog" class="button primary">
                    <i class="bi bi-upload"></i>
                    Upload Media
                </button>
            </div>
        </div>
        
        <div class="media-grid" id="media_grid">
            {{#media_items}}
            <div class="media-item" data-id="{{{MEDIA_ID}}}" draggable="true">
                <img src="{{{THUMBNAIL_URL}}}" alt="{{{MEDIA_NAME}}">
                <div class="media-name">{{{MEDIA_NAME}}}</div>
                <div class="media-info">{{{MEDIA_SIZE}}} • {{{MEDIA_DURATION}}}</div>
            </div>
            {{/media_items}}
        </div>
    </section>
</main>
```

## Phase 7: Final Polish & Responsiveness (Day 14)

### Task 7.1: Add Responsive Design
**File:** `public/css/theme-base.css`

**Add responsive utilities:**
```css
/* Responsive Design System */
@media (max-width: 1200px) {
    .form-grid-4 {
        grid-template-columns: repeat(3, 1fr);
    }
    
    .dashboards {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    }
}

@media (max-width: 992px) {
    .form-grid-3,
    .form-grid-4 {
        grid-template-columns: repeat(2, 1fr);
    }
    
    header nav {
        padding: var(--gh-spacing-sm) var(--gh-spacing-md);
    }
    
    header nav .menu {
        gap: var(--gh-spacing-md);
    }
}

@media (max-width: 768px) {
    .form-grid-2,
    .form-grid-3,
    .form-grid-4 {
        grid-template-columns: 1fr;
    }
    
    .dashboards {
        grid-template-columns: 1fr;
    }
    
    .datatable-toolbar {
        flex-direction: column;
        gap: var(--gh-spacing-md);
        align-items: stretch;
    }
    
    .datatable-filters,
    .datatable-actions {
        justify-content: center;
    }
    
    .form-actions {
        flex-direction: column;
        gap: var(--gh-spacing-sm);
    }
    
    .form-actions button {
        width: 100%;
    }
}

@media (max-width: 576px) {
    :root {
        --gh-spacing-lg: 1rem;
        --gh-spacing-xl: 1.5rem;
    }
    
    header nav {
        flex-direction: column;
        gap: var(--gh-spacing-md);
        padding: var(--gh-spacing-md);
    }
    
    header nav .menu {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .card-header,
    .card-body,
    .card-footer {
        padding: var(--gh-spacing-md);
    }
}
```

### Task 7.2: Add Loading States & Animations
**File:** `public/css/theme-base.css`

**Add animation system:**
```css
/* Loading States & Animations */
.loading {
    position: relative;
    pointer-events: none;
}

.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid var(--gh-border-color);
    border-top-color: var(--gh-primary-color);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Fade In Animation */
.fade-in {
    animation: fadeIn var(--gh-transition-normal) ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Slide In Animation */
.slide-in {
    animation: slideIn var(--gh-transition-normal) ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Pulse Animation */
.pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

/* Shake Animation */
.shake {
    animation: shake 0.5s ease-in-out;
}

@keyframes shake {
    0%, 100% {
        transform: translateX(0);
    }
    25% {
        transform: translateX(-5px);
    }
    75% {
        transform: translateX(5px);
    }
}
```

## Implementation Checklist

### Phase 1: Foundation ✅
- [ ] Update CSS custom properties
- [ ] Modernize typography system
- [ ] Create card component system

### Phase 2: Navigation ✅
- [ ] Modernize header navigation
- [ ] Update header template
- [ ] Test dropdown functionality

### Phase 3: Forms ✅
- [ ] Modernize form elements
- [ ] Create form layout utilities
- [ ] Test form responsiveness

### Phase 4: Datatables ✅
- [ ] Modernize datatable system
- [ ] Update datatable template
- [ ] Test pagination and sorting

### Phase 5: Dashboards ✅
- [ ] Modernize dashboard layout
- [ ] Update dashboard template
- [ ] Test widget responsiveness

### Phase 6: Media Interface ✅
- [ ] Modernize media grid
- [ ] Update media template
- [ ] Test drag and drop

### Phase 7: Polish ✅
- [ ] Add responsive design
- [ ] Add loading states
- [ ] Test cross-browser compatibility

## Testing Checklist

### Visual Testing
- [ ] Check all color schemes in light/dark modes
- [ ] Verify typography hierarchy
- [ ] Test card hover effects
- [ ] Validate button states

### Responsive Testing
- [ ] Test on mobile devices (320px+)
- [ ] Test on tablets (768px+)
- [ ] Test on desktop (1200px+)
- [ ] Verify navigation collapse

### Functional Testing
- [ ] Test form submissions
- [ ] Verify datatable sorting
- [ ] Check pagination
- [ ] Test media uploads

### Browser Testing
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)

## Performance Considerations

1. **CSS Optimization**: Use CSS custom properties for consistent theming
2. **Image Optimization**: Implement lazy loading for media thumbnails
3. **JavaScript**: Minimize DOM manipulation, use event delegation
4. **Caching**: Implement proper cache headers for static assets

## Accessibility Improvements

1. **Color Contrast**: Ensure WCAG AA compliance with new color scheme
2. **Keyboard Navigation**: Test all interactive elements
3. **Screen Readers**: Add proper ARIA labels and roles
4. **Focus Management**: Implement visible focus indicators

## Future Enhancements

1. **Dark Mode Toggle**: Add user preference for dark/light themes
2. **Customizable Dashboard**: Allow users to rearrange dashboard widgets
3. **Advanced Filtering**: Implement saved filters and search history
4. **Real-time Updates**: Add WebSocket support for live data updates

