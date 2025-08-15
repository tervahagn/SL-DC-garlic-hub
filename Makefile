# Makefile for CSS minification and other build tasks

CSS_SRC_DIR := public/css
MINIFY_TOOL := tools/minify-css.sh

CSS_FILES := $(wildcard $(CSS_SRC_DIR)/*.css)
MIN_CSS_FILES := $(CSS_FILES:.css=.min.css)

.PHONY: minify-css
minify-css:
	@echo "Minifying CSS files..."
	@for file in $(CSS_FILES); do \
		$(MINIFY_TOOL) $$file $${file%.css}.min.css; \
	done

.PHONY: build-css
build-css:
	@echo "Building and minifying CSS with PostCSS..."
	@npm run build:css
