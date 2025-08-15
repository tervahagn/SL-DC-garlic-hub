# UI Style Guide (summary)

This is a compact reference for the UI modernization changes and the new design tokens introduced in `public/css/theme-base.css`.

## Design tokens (high level)
- Colors
  - --color-primary: #1e40af
  - --color-secondary: #0ea5e9
  - --color-accent: #06b6d4
  - --color-success / --color-warning / --color-danger
  - Neutral palette: --neutral-100 .. --neutral-500
  - Semantic text/background: --text-primary/--text-secondary/--text-muted, --bg-primary/--bg-secondary/--bg-tertiary
- Spacing
  - --spacing-xs, --spacing-sm, --spacing-base, --spacing-lg, --spacing-xl, --spacing-2xl
- Radii
  - --radius-sm, --radius-md, --radius-lg, --radius-xl
- Shadows
  - --shadow-sm, --shadow-md, --shadow-lg, --shadow-xl
- Typography
  - Inter font is used (imported in `theme-base.css`)
  - font-size vars: --font-size-xs .. --font-size-3xl
  - font-weight vars: --font-weight-light .. --font-weight-bold

## Key components and classes
- `.card` — base card container. Use for panels and grouped content.
  - Subcomponents: `.card-header`, `.card-body`, `.card-footer`
- Buttons
  - `.btn`, size variants: `.btn-small`, `.btn-base`, `.btn-large`
  - Variants: `.btn-primary`, `.btn-secondary`, `.btn-outline`
- Forms
  - Grid helpers: `.form-grid`, `.form-grid-2`, `.form-grid-3`, `.form-grid-4`
  - Field wrapper: `.field-wrapper` (used in datatable and forms)
- Datatables
  - Wrap table/list pages with the `{{> datatable}}` partial (located at `templates/generic/datatable.mustache`) which produces `.datatable`, `.datatable-header`, `.datatable-toolbar`, `form#form_elements_search`, and `.results-listing`.
  - JS expectations: `public/js/main.js` listens for `.datatable` containers, submits `form_elements_search` when per-page or pager controls are used.
- Dashboard
  - `.dashboards` container uses CSS grid. Child widgets use `.dashboard` and `.dashboard-stats` / `.stat-item`.
- Media grid
  - `.media-grid` / `.media-item` implemented in `public/css/mediapool/overview.css`
- Playlists
  - Playlist editor containers use `.playlist-editor`, `.playlist-items`, `.playlist-item`, `.drag-handle`.
  - Client DnD: `public/js/playlist-dnd.js` will update a hidden `playlist_order` input on reorder.

## Accessibility notes
- Header toggle uses `button#mobileMenuToggle` with `aria-controls="mainMenu"` and `aria-expanded`.
- Dropdown buttons use `aria-haspopup="true"` and `aria-expanded` managed by `public/js/main.js`.
- Prefer using semantic elements and `role` attributes in generated templates (e.g., role="menubar" / role="menuitem" / role="menu").

## How to use
- Prefer the `{{> datatable}}` partial for any paginated list view.
- Use token variables for spacing/typography and avoid hard-coded colors/sizes.
- For playlist reorder, include playlist items with a `data-id` (or `data-id` attribute or `id`) so `playlist-dnd.js` can collect the order.

## Files of interest
- `public/css/theme-base.css` — tokens & components
- `templates/generic/datatable.mustache` — datatable partial
- `public/js/main.js` — menu + datatable toolbar logic
- `public/js/playlist-dnd.js` — playlist DnD helper

## CSS Minification

For production deployments, you can generate optimized and vendor-prefixed CSS by running:

```sh
make build-css
```

This uses PostCSS (autoprefixer & cssnano) and `tools/minify-css.sh` to create `.min.css` files alongside your original stylesheets in `public/css`.
