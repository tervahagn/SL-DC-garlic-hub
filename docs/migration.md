# UI Modernization — Migration Notes

This file lists breaking changes and recommended migration steps for upgrading templates and custom CSS to the new design system.

## Breaking changes
- Colors: avoid referencing old hex values directly; prefer `--color-primary`, `--text-primary`, `--bg-primary` etc.
- Buttons: replace legacy `.button` or other custom classes with `.btn` and appropriate variants (`.btn-primary`, `.btn-secondary`).
- Datatables: prefer using the `{{> datatable}}` partial. If your template builds a custom toolbar, ensure it includes `form#form_elements_search` and the hidden inputs `elements_page` and `elements_per_page`.
- Playlists: playlist items should expose an identifier via `data-id` or `id` to support client-side DnD ordering.

## Quick migration checklist
- [ ] Replace manual header markup with `templates/layouts/main_layout.mustache` copy if heavily customized.
- [ ] Update CSS references to tokens instead of old class names.
- [ ] Ensure `form_elements_search` exists on list pages for toolbar behavior to work.
- [ ] Test mobile menu toggle and dropdowns (JS handles aria attributes).

## Troubleshooting tips
- If datatable paging seems broken after migration, check that `elements_per_page` select exists and the server accepts `elements_page` / `elements_per_page` query params.
- If playlist order is not posted, ensure `playlist_order` hidden input is present or `playlist-dnd.js` was loaded (imported in main layout) and that `.playlist-item` elements contain `data-id`.

