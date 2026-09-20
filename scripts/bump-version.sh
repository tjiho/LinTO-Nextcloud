#!/usr/bin/env bash
# Bumps the app version everywhere it is recorded:
#
#   - linto/appinfo/info.xml   <version>, and the tag pinned in the
#                              <screenshot> URLs so they keep resolving
#   - linto/package.json       "version" (and package-lock.json, via npm)
#
# It only edits files; committing and tagging is scripts/release.sh's job.
#
# Usage:
#   ./scripts/bump-version.sh 1.0.1
set -euo pipefail

root_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
app_dir="$root_dir/linto"
cd "$root_dir"

new_version="${1-}"
if [ -z "$new_version" ]; then
  echo "Usage: $0 <version>" >&2
  exit 1
fi

# Same grammar the app store's info.xsd enforces, so a bad version is caught
# here rather than at upload time.
if ! printf '%s' "$new_version" \
  | grep -Eq '^(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)(-[0-9A-Za-z-]+(\.[0-9A-Za-z-]+)*)?$'; then
  echo "Not a valid version: $new_version (expected MAJOR.MINOR.PATCH[-prerelease])" >&2
  exit 1
fi

old_version="$(sed -n 's:.*<version>\(.*\)</version>.*:\1:p' "$app_dir/appinfo/info.xml")"
if [ -z "$old_version" ]; then
  echo "Could not read <version> from appinfo/info.xml" >&2
  exit 1
fi
if [ "$old_version" = "$new_version" ]; then
  echo "Already at $new_version — nothing to do."
  exit 0
fi

# package-lock.json repeats the version for every dependency that happens to
# share it, so it cannot be sed-ed: npm rewrites exactly the two root fields.
(cd "$app_dir" && npm version "$new_version" --no-git-tag-version --allow-same-version >/dev/null)

# <version> is the only element with that name; the screenshot URLs pin the
# release tag, which scripts/release.sh is about to create under the new name.
python3 - "$app_dir/appinfo/info.xml" "$old_version" "$new_version" <<'PY'
import io, re, sys

path, old, new = sys.argv[1], sys.argv[2], sys.argv[3]
xml = io.open(path, encoding='utf-8').read()

xml, n = re.subn(r'<version>%s</version>' % re.escape(old),
                 '<version>%s</version>' % new, xml)
if n != 1:
    sys.exit('expected exactly one <version>%s</version>, found %d' % (old, n))

# Matches every raw.githubusercontent URL pinned to the old tag, in element
# text and in small-thumbnail attributes alike.
xml, n = re.subn(r'(https://raw\.githubusercontent\.com/[^/]+/[^/]+/)%s/' % re.escape(old),
                 lambda m: m.group(1) + new + '/', xml)
print('  %d screenshot URL(s) repinned' % n)

io.open(path, 'w', encoding='utf-8').write(xml)
PY

if ! grep -q "^## \[$new_version\]" "$app_dir/CHANGELOG.md"; then
  echo "Reminder: linto/CHANGELOG.md has no '## [$new_version]' section yet —"
  echo "add one before running scripts/release.sh, or the release notes will be empty."
  echo
fi

echo "Bumped $old_version -> $new_version:"
git --no-pager diff --stat -- linto/appinfo/info.xml linto/package.json linto/package-lock.json
