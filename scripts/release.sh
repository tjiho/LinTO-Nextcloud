#!/usr/bin/env bash
# Cuts a release of the LinTO Nextcloud app:
#
#   1. builds the store tarball (scripts/build-release.sh)
#   2. commits the working tree as "Release <version>"
#   3. tags that commit <version> and pushes both
#   4. publishes a GitHub release with the tarball attached (needs the gh CLI)
#
# The version is the one in linto/appinfo/info.xml — bump it there, and add the
# matching "## [<version>]" section to linto/CHANGELOG.md, before running this.
#
# Usage:
#   ./scripts/release.sh [-y|--yes]
set -euo pipefail

root_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
app_dir="$root_dir/linto"
dist_dir="$root_dir/dist"
cd "$root_dir"

assume_yes=0
case "${1-}" in
  -y|--yes) assume_yes=1 ;;
  "") ;;
  *) echo "Usage: $0 [-y|--yes]" >&2; exit 1 ;;
esac

version="$(sed -n 's:.*<version>\(.*\)</version>.*:\1:p' "$app_dir/appinfo/info.xml")"
if [ -z "$version" ]; then
  echo "Could not read <version> from appinfo/info.xml" >&2
  exit 1
fi

branch="$(git rev-parse --abbrev-ref HEAD)"
if [ "$branch" = "HEAD" ]; then
  echo "Detached HEAD — check out a branch before releasing." >&2
  exit 1
fi

# Everything that can be checked cheaply is checked up front: a release that
# dies halfway leaves a commit and a tag behind that have to be unpicked by hand.
# The fetch pulls origin's tags into refs/tags, so one test covers both sides.
git fetch --quiet --tags origin
if git rev-parse -q --verify "refs/tags/$version" >/dev/null; then
  echo "Tag $version already exists — bump <version> in appinfo/info.xml first." >&2
  exit 1
fi

# Release notes are the CHANGELOG section for this version, minus its heading.
mkdir -p "$dist_dir"
notes_file="$dist_dir/release-notes-$version.md"
awk -v v="$version" '
  $0 ~ "^## \\[" v "\\]" { found = 1; next }
  found && /^## / { exit }
  found { print }
' "$app_dir/CHANGELOG.md" | sed -e '/./,$!d' > "$notes_file"
if [ ! -s "$notes_file" ]; then
  echo "Warning: no '## [$version]' section in linto/CHANGELOG.md — release notes will be empty."
fi

echo
echo "Releasing linto $version from branch $branch"
echo
echo "Working tree to be committed:"
git status --short
echo
echo "  build    dist/linto-$version.tar.gz"
echo "  commit   Release $version"
echo "  tag      $version"
echo "  push     origin $branch, origin $version"
echo "  publish  GitHub release $version"
echo
if [ "$assume_yes" -ne 1 ]; then
  read -r -p "Continue? [y/N] " reply
  case "$reply" in
    [yY]|[yY][eE][sS]) ;;
    *) echo "Aborted."; exit 1 ;;
  esac
fi

"$root_dir/scripts/build-release.sh"

tarball="$dist_dir/linto-$version.tar.gz"
if [ ! -f "$tarball" ]; then
  echo "Build did not produce $tarball" >&2
  exit 1
fi

git add -A
if git diff --cached --quiet; then
  echo "Nothing to commit — tagging the current HEAD."
else
  git commit -m "Release $version"
fi

git tag -a "$version" -m "Release $version"
git push origin "$branch"
git push origin "$version"

# A missing gh is not a failure: the tag is pushed and the tarball is on disk,
# so the release can still be published by hand.
if command -v gh >/dev/null 2>&1 && gh auth status >/dev/null 2>&1; then
  gh release create "$version" "$tarball" --title "$version" --notes-file "$notes_file"
else
  repo_url="$(git remote get-url origin | sed -e 's:^git@github\.com\::https://github.com/:' -e 's:\.git$::')"
  echo
  echo "gh CLI missing or not logged in — GitHub release not published."
  echo "Publish it with:"
  echo "  gh release create $version $tarball --title $version --notes-file $notes_file"
  echo "or at $repo_url/releases/new?tag=$version"
fi

echo
echo "Done. Upload $tarball to https://apps.nextcloud.com/developer/apps/releases/new"
