#!/usr/bin/env bash
# Simple, dependency-free CSS minifier: removes comments and extra whitespace
# Usage: ./tools/minify-css.sh input.css output.min.css
set -euo pipefail
if [ "$#" -ne 2 ]; then
  echo "Usage: $0 <input.css> <output.min.css>"
  exit 2
fi
infile="$1"
outfile="$2"
if [ ! -f "$infile" ]; then
  echo "Input file not found: $infile"
  exit 3
fi
# remove comments, newlines, multiple spaces
cat "$infile" \
  | sed -E 's:/\*[^*]*\*+([^/*][^*]*\*+)*/::g' \
  | tr '\n' ' ' \
  | sed -E 's/\s{2,}/ /g' \
  | sed -E 's/\s*([{};:,>+~\(\)])\s*/\1/g' \
  | sed -E 's/;}/}/g' \
  > "$outfile"

echo "Minified $infile -> $outfile"
