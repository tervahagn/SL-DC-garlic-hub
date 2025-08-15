# Garlic Hub UI Modernization - Tasks List

## Phase 1: Foundation & Design System (Days 1-3)

### Task 1.1: Update CSS Custom Properties
**File:** `public/css/theme-base.css`
**Estimated Time:** 2 hours
**Priority:** Critical

- [ ] Replace existing `:root` variables with Enplug-style color palette
- [ ] Add new color variables for primary (#1e40af), secondary (#0ea5e9), accent (#06b6d4)
- [ ] Add semantic color variables (success, warning, danger)
- [ ] Add neutral color variables (text-primary, text-secondary, text-muted)
- [ ] Add background color variables (bg-primary, bg-secondary, bg-tertiary)
- [ ] Add shadow variables (shadow-sm, shadow-md, shadow-lg, shadow-xl)
- [ ] Add spacing variables (spacing-xs through spacing-2xl)
- [ ] Add border radius variables (radius-sm through radius-xl)
- [ ] Add transition variables (transition-fast, transition-normal, transition-slow)

### Task 1.2: Import Modern Typography
**File:** `public/css/theme-base.css`
**Estimated Time:** 1 hour
**Priority:** High

- [ ] Add Google Fonts import for Inter font family
- [ ] Update font-family variables to use Inter with system font fallbacks
- [ ] Add font-size variables (font-size-xs through font-size-3xl)
- [ ] Add font-weight variables (font-weight-light through font-weight-bold)
- [ ] Add line-height variables (line-height-tight, normal, relaxed)

### Task 1.3: Update Base Typography Styles
**File:** `public/css/theme-base.css`
**Estimated Time:** 1 hour
**Priority:** High

- [ ] Update `html` selector with new font-family and color variables
- [ ] Update `body` selector with new background and text colors
- [ ] Update heading selectors (h1-h6) with new font weights and sizes
- [ ] Update paragraph and text element styles
- [ ] Remove old OpenSans font references

### Task 1.4: Create Modern Card Component System
**File:** `public/css/theme-base.css`
**Estimated Time:** 2 hours
**Priority:** High

- [ ] Create `.card` base class with background, border, shadow
- [ ] Add `.card:hover` effects with transform and shadow changes
- [ ] Create `.card-header` with padding and border-bottom
- [ ] Create `.card-body` with consistent padding
- [ ] Create `.card-footer` with top border and background
- [ ] Update `.main-element-group` to use new card styling
- [ ] Add hover effects to main-element-group

## Phase 2: Navigation & Header Redesign (Days 4-5)

### Task 2.1: Modernize Header Styles
**File:** `public/css/theme-base.css`
**Estimated Time:** 3 hours
**Priority:** High

- [ ] Update `header` selector with sticky positioning and modern styling
- [ ] Update `header nav` with flexbox layout and max-width container
- [ ] Style logo section with proper alignment
- [ ] Update main menu styling with modern spacing and typography
- [ ] Create dropdown menu styles with animations and shadows
- [ ] Add hover and focus states for menu items
- [ ] Ensure mobile responsiveness for header

### Task 2.2: Update Header Template Structure
**File:** `templates/layouts/main_layout.mustache`
**Estimated Time:** 2 hours
**Priority:** High

- [ ] Wrap logo in semantic div with class="logo"
- [ ] Add icon placeholders to menu items ({{ICON_NAME}})
- [ ] Update language selector with modern structure
- [ ] Update user menu with icons and improved hierarchy
- [ ] Add proper ARIA labels for accessibility
- [ ] Ensure semantic HTML structure

### Task 2.3: Create Mobile Navigation
**File:** `public/css/theme-base.css`
**Estimated Time:** 2 hours
**Priority:** Medium

- [ ] Add mobile menu toggle button styles
- [ ] Create mobile menu overlay styles
- [ ] Add responsive breakpoints for navigation
- [ ] Ensure touch-friendly target sizes (44px minimum)
- [ ] Test mobile menu functionality

## Phase 3: Form & Input Modernization (Days 6-7)

### Task 3.1: Modernize Form Input Styles
**File:** `public/css/theme-base.css`
**Estimated Time:** 2 hours
**Priority:** High

- [ ] Update input field styles with new border and padding variables
- [ ] Add focus states with border color and box-shadow
- [ ] Update hover states for better user feedback
- [ ] Style textarea elements consistently
- [ ] Update select dropdown styling
- [ ] Add disabled state styling

### Task 3.2: Create Modern Button System
**File:** `public/css/theme-base.css`
**Estimated Time:** 2 hours
**Priority:** High

- [ ] Create base button styles with flexbox layout
- [ ] Add primary button variant with brand colors
- [ ] Add secondary button variant with outline style
- [ ] Create button size variants (small, base, large)
- [ ] Add hover and active states with transforms
- [ ] Update existing button classes to use new system
- [ ] Style naked-button class consistently

### Task 3.3: Enhance Form Layout System
**File:** `public/css/theme-base.css`
**Estimated Time:** 2 hours
**Priority:** Medium

- [ ] Create form grid system (form-grid, form-grid-2, form-grid-3, form-grid-4)
- [ ] Add responsive breakpoints for form grids
- [ ] Update fieldset styling with modern borders and backgrounds
- [ ] Style form legends with proper typography
- [ ] Create form-actions layout with flexbox
- [ ] Update field-wrapper spacing and organization

### Task 3.4: Modernize Dialog/Modal Styles
**File:** `public/css/theme-base.css`
**Estimated Time:** 1.5 hours
**Priority:** Medium

- [ ] Update dialog styles with modern shadows and borders
- [ ] Style dialog header with brand colors
- [ ] Update dialog footer with proper spacing
- [ ] Enhance dialog backdrop with better opacity
- [ ] Style dialog buttons consistently
- [ ] Add close button styling

## Phase 4: Datatable & Grid Modernization (Days 8-9)

### Task 4.1: Create Modern Datatable Container
**File:** `public/css/theme-base.css`
**Estimated Time:** 2 hours
**Priority:** High

- [ ] Create `.datatable` container with card styling
- [ ] Add `.datatable-header` with title and subtitle areas
- [ ] Create `.datatable-toolbar` with filters and actions
- [ ] Style `.datatable-filters` with flexbox layout
- [ ] Style `.datatable-actions` with button groups
- [ ] Add responsive behavior for toolbar

### Task 4.2: Modernize Results Listing
**File:** `public/css/theme-base.css`
**Estimated Time:** 3 hours
**Priority:** High

- [ ] Update `.results-listing` with modern spacing
- [ ] Redesign `.results-header` with grid layout
- [ ] Update `.results-body` with hover effects and grid
- [ ] Style action buttons with consistent sizing
- [ ] Add sorting indicators and hover states
- [ ] Ensure mobile responsiveness for tables
- [ ] Add horizontal scrolling for mobile

### Task 4.3: Create Pagination System
**File:** `public/css/theme-base.css`
**Estimated Time:** 1.5 hours
**Priority:** Medium

- [ ] Create `.datatable-pagination` container
- [ ] Style pagination info text
- [ ] Create pagination controls with modern buttons
- [ ] Add current page highlighting
- [ ] Style previous/next navigation
- [ ] Ensure mobile-friendly pagination

### Task 4.4: Update Datatable Template
**File:** `templates/generic/datatable.mustache` (if exists)
**Estimated Time:** 2 hours
**Priority:** Medium

- [ ] Wrap existing table in datatable container
- [ ] Add datatable-header section
- [ ] Create datatable-toolbar structure
- [ ] Update form layout within toolbar
- [ ] Add proper semantic structure
- [ ] Ensure accessibility compliance

## Phase 5: Dashboard & Widget System (Days 10-11)

### Task 5.1: Modernize Dashboard Layout
**File:** `public/css/theme-base.css`
**Estimated Time:** 2 hours
**Priority:** Medium

- [ ] Update `.dashboards` with CSS Grid layout
- [ ] Create responsive grid columns (auto-fit, minmax)
- [ ] Add proper gap spacing between dashboard items
- [ ] Ensure mobile responsiveness

### Task 5.2: Create Dashboard Widget Components
**File:** `public/css/theme-base.css`
**Estimated Time:** 2 hours
**Priority:** Medium

- [ ] Update `.dashboard` class with card styling
- [ ] Add hover effects and transforms
- [ ] Create `.dashboard-header` with proper styling
- [ ] Style `.dashboard-body` with consistent padding
- [ ] Update dashboard list styling
- [ ] Create dashboard statistics grid

### Task 5.3: Add Dashboard Statistics Components
**File:** `public/css/theme-base.css`
**Estimated Time:** 1.5 hours
**Priority:** Low

- [ ] Create `.dashboard-stats` grid container
- [ ] Style `.stat-item` components
- [ ] Create `.stat-value` with large typography
- [ ] Style `.stat-label` with secondary text
- [ ] Add responsive behavior for stats

### Task 5.4: Update Dashboard Template
**File:** `templates/dashboard.mustache`
**Estimated Time:** 1 hour
**Priority:** Medium

- [ ] Add page-header section
- [ ] Wrap dashboards in proper container
- [ ] Add dashboard-header and dashboard-body structure
- [ ] Include statistics template sections
- [ ] Ensure proper semantic markup

## Phase 6: Media Management Interface (Days 12-13)

### Task 6.1: Create Media Grid Layout
**File:** `public/css/mediapool/overview.css`
**Estimated Time:** 3 hours
**Priority:** Medium

- [ ] Create CSS Grid layout for main content area
- [ ] Define grid areas (sidebar, header, content)
- [ ] Style sidebar with modern card design
- [ ] Create content area with proper spacing
- [ ] Add responsive behavior for mobile

### Task 6.2: Modernize Media Grid Items
**File:** `public/css/mediapool/overview.css`
**Estimated Time:** 2 hours
**Priority:** Medium

- [ ] Create `.media-grid` with auto-fill columns
- [ ] Style `.media-item` with card design
- [ ] Add hover effects and transforms
- [ ] Style media thumbnails consistently
- [ ] Add media item metadata styling
- [ ] Ensure touch-friendly interactions

### Task 6.3: Update Sidebar Navigation
**File:** `public/css/mediapool/overview.css`
**Estimated Time:** 2 hours
**Priority:** Medium

- [ ] Style sidebar header and title
- [ ] Update tree filter input styling
- [ ] Modernize tree navigation appearance
- [ ] Add hover states for navigation items
- [ ] Ensure proper spacing and typography

### Task 6.4: Create Upload Actions Interface
**File:** `public/css/mediapool/overview.css`
**Estimated Time:** 1.5 hours
**Priority:** Low

- [ ] Style content header with flexbox
- [ ] Create upload actions button group
- [ ] Style current path breadcrumb
- [ ] Add responsive behavior
- [ ] Ensure consistent button styling

## Phase 7: Playlist Interface Enhancement (Days 14-15)

### Task 7.1: Modernize Playlist Editor
**File:** `public/css/playlists/` (various files)
**Estimated Time:** 3 hours
**Priority:** Medium

- [ ] Update playlist container styling
- [ ] Modernize playlist item cards
- [ ] Style drag-and-drop indicators
- [ ] Update timeline/sequence view
- [ ] Add hover and active states
- [ ] Ensure mobile responsiveness

### Task 7.2: Update Playlist Forms
**File:** `public/css/playlists/` (various files)
**Estimated Time:** 2 hours
**Priority:** Medium

- [ ] Apply new form styling to playlist forms
- [ ] Update media selection interface
- [ ] Style playlist settings panels
- [ ] Modernize save/export buttons
- [ ] Add validation feedback styling

## Phase 8: Polish & Optimization (Days 16-17)

### Task 8.1: Add Micro-interactions
**File:** `public/css/theme-base.css`
**Estimated Time:** 2 hours
**Priority:** Low

- [ ] Add subtle loading animations
- [ ] Create hover transitions for interactive elements
- [ ] Add focus animations for better accessibility
- [ ] Style progress indicators
- [ ] Add success/error state animations

### Task 8.2: Optimize CSS Performance
**Files:** All CSS files
**Estimated Time:** 2 hours
**Priority:** Low

- [ ] Remove unused CSS rules
- [ ] Optimize CSS selectors for performance
- [ ] Minimize CSS custom property usage where possible
- [ ] Add CSS minification for production
- [ ] Test performance impact

### Task 8.3: Cross-browser Testing & Fixes
**Files:** Various CSS files
**Estimated Time:** 4 hours
**Priority:** High

- [ ] Test in Chrome, Firefox, Safari, Edge
- [ ] Fix any browser-specific issues
- [ ] Add vendor prefixes where needed
- [ ] Test on mobile devices
- [ ] Validate CSS syntax

### Task 8.4: Accessibility Audit & Fixes
**Files:** CSS and template files
**Estimated Time:** 3 hours
**Priority:** High

- [ ] Run accessibility audit tools
- [ ] Fix color contrast issues
- [ ] Improve focus indicators
- [ ] Add missing ARIA labels
- [ ] Test keyboard navigation
- [ ] Validate with screen readers

## Quality Assurance Tasks

### QA Task 1: Visual Regression Testing
**Estimated Time:** 4 hours
**Priority:** Critical

- [ ] Test all major pages for visual consistency
- [ ] Compare before/after screenshots
- [ ] Verify responsive design on multiple devices
- [ ] Check print styles if applicable
- [ ] Document any visual issues

### QA Task 2: Functional Testing
**Estimated Time:** 6 hours
**Priority:** Critical

- [ ] Test all forms and input validation
- [ ] Verify all buttons and links work
- [ ] Test file upload functionality
- [ ] Verify data table sorting and pagination
- [ ] Test user authentication flows
- [ ] Check media management operations

### QA Task 3: Performance Testing
**Estimated Time:** 2 hours
**Priority:** High

- [ ] Run Lighthouse audits on key pages
- [ ] Measure CSS bundle size impact
- [ ] Test page load times
- [ ] Check for layout shift issues
- [ ] Verify smooth animations

## Documentation Tasks

### Doc Task 1: Update Style Guide
**Estimated Time:** 2 hours
**Priority:** Medium

- [ ] Document new color palette
- [ ] Create component usage examples
- [ ] Update typography guidelines
- [ ] Document spacing system
- [ ] Create developer guidelines

### Doc Task 2: Create Migration Guide
**Estimated Time:** 1 hour
**Priority:** Low

- [ ] Document breaking changes
- [ ] Provide upgrade instructions
- [ ] List deprecated classes
- [ ] Create troubleshooting guide

## Total Estimated Time: ~65 hours (13 working days)

## Task Dependencies

### Critical Path:
1. Task 1.1 (CSS Variables) → All other tasks depend on this
2. Task 1.2-1.3 (Typography) → Required for all text styling
3. Task 2.1-2.2 (Header) → Affects all page layouts
4. Task 3.1-3.2 (Forms) → Required for all form pages

### Parallel Work Possible:
- Media management tasks (6.x) can be done in parallel with dashboard tasks (5.x)
- Playlist tasks (7.x) can be done independently
- QA tasks can run in parallel with development
- Documentation can be done throughout the project