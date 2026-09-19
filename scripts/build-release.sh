#!/usr/bin/env bash
# Builds a release tarball for the LinTO Nextcloud app, packaged the way the
# Nextcloud app store expects: a tar.gz whose root is a single "linto/"
# directory holding only what the app needs at runtime — no dev tooling, no
# JS/PHP sources that get compiled away.
#
# Usage:
#   ./scripts/build-release.sh
#
# Produces: dist/linto-<version>.tar.gz (version read from appinfo/info.xml)
set -euo pipefail

root_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
app_dir="$root_dir/linto"
dist_dir="$root_dir/dist"

version="$(sed -n 's:.*<version>\(.*\)</version>.*:\1:p' "$app_dir/appinfo/info.xml")"
if [ -z "$version" ]; then
  echo "Could not read <version> from appinfo/info.xml" >&2
  exit 1
fi

echo "Building JS/CSS assets for linto $version..."
# @nextcloud/vite-config doesn't empty js/ and css/ before building (outDir
# sits at the project root, which Vite treats as unsafe to auto-clean), so
# stale hashed chunks from previous builds would otherwise pile up in the
# release tarball forever.
rm -rf "$app_dir/js" "$app_dir/css"
(cd "$app_dir" && npm ci && npm run build)

stage_dir="$(mktemp -d)"
trap 'rm -rf "$stage_dir"' EXIT

echo "Staging release files..."
target_dir="$stage_dir/linto"
mkdir -p "$target_dir"

# Runtime files only — no src/, no dev tooling, no PHP/JS package manifests.
# composer.json has no real runtime dependency (its "require" is just the
# composer-bin-plugin dev tool), so there is no vendor/ to ship either.
include=(
  appinfo
  lib
  templates
  img
  l10n
  js
  css
  LICENSE
  CHANGELOG.md
)
for item in "${include[@]}"; do
  cp -r "$app_dir/$item" "$target_dir/"
done

mkdir -p "$dist_dir"
tar_path="$dist_dir/linto-$version.tar.gz"
tar -czf "$tar_path" -C "$stage_dir" linto

echo
echo "Release tarball: $tar_path"
