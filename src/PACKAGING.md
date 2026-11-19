# Building Hestia packages

Hestia ships 4 Debian packages:

- `hestia` — the Control Panel itself.
- `hestia-nginx` — the bundled nginx engine.
- `hestia-php` — the bundled PHP engine.
- `hestia-web-terminal` — the web terminal websocket backend.

Each one has its own packaging recipe under `src/deb/<pkg>/control`, with an
upstream `Version:` field (e.g. `1.10.9` or `1.10.9~beta1`) that should only
change when the actual software version changes.

The final `.deb` version always looks like:

```text
<upstream_version>-<pkgrev>+<distro_suffix>[~<release_tag>]
```

e.g. `1.10.9-1+debian12` or `1.10.9-2+ubuntu24.04~beta1`.

- `<pkgrev>` is the **package revision**: it starts at `1` and must be bumped
  whenever a package needs to be rebuilt/republished for the _same_ upstream
  version (e.g. a packaging-only fix, not a code change).
- `<distro_suffix>` (`debian12`, `ubuntu24.04`, ...) is derived automatically
  from the OS being built for.
- `<release_tag>` is an optional suffix used to mark non-official/CI/
  prerelease builds.

## 1. Local / manual builds

### `src/hst_autocompile.sh`

Builds one or more packages for the **current** OS/architecture only.

| Flag                                                          | Effect                                                                                                         |
| ------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------- |
| `--all` / `--hestia` / `--nginx` / `--php` / `--web-terminal` | Select which package(s) to build.                                                                              |
| `--pkgrev <n>`                                                | Revision applied to **all** packages being built in this invocation (default `1`).                             |
| `--pkgrev-hestia <n>`                                         | Revision for `hestia` only, overrides `--pkgrev` for that package.                                             |
| `--pkgrev-nginx <n>`                                          | Revision for `hestia-nginx` only, overrides `--pkgrev` for that package.                                       |
| `--pkgrev-php <n>`                                            | Revision for `hestia-php` only, overrides `--pkgrev` for that package.                                         |
| `--pkgrev-web-terminal <n>`                                   | Revision for `hestia-web-terminal` only, overrides `--pkgrev` for that package.                                |
| `--release <id>`                                              | Appends `~<id>` to the version, for CI/prerelease builds.                                                      |
| `--install` / `--noinstall`                                   | Install the built package(s) afterwards or not.                                                                |
| `--keepbuild`                                                 | Keep downloaded sources/build folders between runs (faster iteration).                                         |
| `--cross`                                                     | Only applies to `--hestia` (the other 3 packages can't cross-build this way); builds for both amd64 and arm64. |

Precedence for the revision of a given package: `--pkgrev-<pkg>` >
`--pkgrev` > default (`1`).

#### Examples

```bash
# Build everything, default revision 1 for all (same as before this feature)
cd src
sudo bash hst_autocompile.sh --all --noinstall --keepbuild '~localsrc'

# Build only hestia-php, bumping it to revision 2 (e.g. a packaging bug was
# fixed without changing the PHP version in src/deb/php/control)
cd src
sudo bash hst_autocompile.sh --php --noinstall --keepbuild --pkgrev-php 2 '~localsrc'

# Build nginx + php + web-terminal, all at revision 3 (e.g. a shared fix
# affecting all three, like a dependency bump)
cd src
sudo bash hst_autocompile.sh --nginx --php --web-terminal --noinstall --keepbuild --pkgrev 3 '~localsrc'

# Build nginx + php together, but only php needs a higher revision than nginx
# (nginx gets revision 1 by default, php gets revision 2)
cd src
sudo bash hst_autocompile.sh --nginx --php --noinstall --keepbuild --pkgrev-php 2 '~localsrc'

# Combine with --release for a prerelease/CI tag
cd src
sudo bash hst_autocompile.sh --php --noinstall --keepbuild --pkgrev-php 2 --release myci1 '~localsrc'
```

### `src/chroot_build_all.sh`

Orchestrates cross-architecture, multi-OS (`--all-os`) builds on a single
machine, using QEMU-emulated chroots, by calling `hst_autocompile.sh`
unmodified inside each one. It accepts the exact same `--pkgrev*` flags and
simply forwards them.

#### Examples

```bash
# Cross-build (all supported OS/arch combos) hestia-php only, revision 2
cd src
sudo bash chroot_build_all.sh --php --pkgrev-php 2 '~localsrc'

# Cross-build everything at revision 1 (default)
cd src
sudo bash chroot_build_all.sh --all '~localsrc'
```

No manual steps are needed beyond choosing the right flag(s): the scripts
produce `.deb`s with the correct `Version:` field directly.

## 2. The `Release` GitHub Actions workflow

File: `.github/workflows/release.yml`. It's a manual (`workflow_dispatch`)
workflow that builds and publishes all 4 packages to the beta or production
apt repository.

The `pkgrev` input accepts two formats:

- **A plain number** (e.g. `"1"`, `"2"`) — applied to all 4 packages.
- **A `pkg=rev` list** (e.g. `"php=2"` or `"php=2,web-terminal=2"`) — bumps
  only the listed packages; any package not listed defaults to `1`. Valid
  package names: `hestia`, `nginx`, `php`, `web-terminal`.

Internally, the `prepare` job parses this into one revision per package and
passes it down to the `build-hestia` and `build-native` jobs.
`prepare-packages.py` then uses each package's own revision to decide
whether to rebuild it or reuse what's already published at that revision (or
higher).

### Examples (full `gh` CLI commands)

```bash
# Normal beta release, every package at revision 1, dry run
gh workflow run release.yml \
	-f channel=beta \
	-f version=1.10.9 \
	-f ref=main \
	-f suites=bookworm,trixie,jammy,noble,resolute \
	-f dry_run=true \
	-f prune=false \
	-f debug=false \
	-f pkgrev=1 \
	-f force=false \
	-f emulate_arm64=false

# Re-cut the whole release at revision 2 for every package, actually publish
gh workflow run release.yml \
	-f channel=production \
	-f version=1.10.9 \
	-f ref=main \
	-f suites=bookworm,trixie,jammy,noble,resolute \
	-f dry_run=false \
	-f prune=false \
	-f debug=false \
	-f pkgrev=2 \
	-f force=false \
	-f emulate_arm64=false

# Only hestia-php needs a rebuild at revision 2; nginx, web-terminal and
# hestia stay at revision 1 and are reused from what's already published
gh workflow run release.yml \
	-f channel=production \
	-f version=1.10.9 \
	-f ref=main \
	-f suites=bookworm,trixie,jammy,noble,resolute \
	-f dry_run=false \
	-f prune=false \
	-f debug=false \
	-f pkgrev=php=2 \
	-f force=false \
	-f emulate_arm64=false

# Both php and web-terminal need bumping, nginx and hestia stay at 1
gh workflow run release.yml \
	-f channel=production \
	-f version=1.10.9 \
	-f ref=main \
	-f suites=bookworm,trixie,jammy,noble,resolute \
	-f dry_run=false \
	-f prune=false \
	-f debug=false \
	-f "pkgrev=php=2,web-terminal=2" \
	-f force=false \
	-f emulate_arm64=false

# hestia itself needs a rebuild at revision 2 (requires the manual step below
# to be done BEFORE running this, or the workflow fails validation)
gh workflow run release.yml \
	-f channel=production \
	-f version=1.10.9 \
	-f ref=main \
	-f suites=bookworm,trixie,jammy,noble,resolute \
	-f dry_run=false \
	-f prune=false \
	-f debug=false \
	-f pkgrev=hestia=2 \
	-f force=false \
	-f emulate_arm64=false
```

### Manual steps still required

1. **If `hestia` itself needs a revision bump** (`pkgrev=hestia=2`, or a
   plain number like `pkgrev=2` that includes it): before running the
   workflow, update `HESTIA_INSTALL_PKGREV='1'` → `'2'` in **both**
   `install/hst-install-debian.sh` and `install/hst-install-ubuntu.sh`,
   commit, then run the workflow against that commit/branch:

   ```bash
   cd /path/to/hestiacp
   sed -i "s/^HESTIA_INSTALL_PKGREV='1'/HESTIA_INSTALL_PKGREV='2'/" \
   	install/hst-install-debian.sh install/hst-install-ubuntu.sh
   git add install/hst-install-debian.sh install/hst-install-ubuntu.sh
   git commit -m "Bump hestia package revision to 2"
   git push
   ```

   The workflow's "Validate install script pkgrev" step checks this and
   **fails the release** if these files don't match the `hestia` revision
   being published — so it's enforced, but still requires this manual commit
   beforehand.

   `hestia-nginx`, `hestia-php` and `hestia-web-terminal` are **not** pinned
   by version in these installer scripts (they're installed unversioned
   alongside the pinned `hestia=<version>`), so bumping their revision never
   requires touching these files.

2. **Picking a high-enough `pkgrev` for reused packages**: if a package was
   already published at revision 2 and you only bump a different one, make
   sure the one you're not rebuilding isn't requested at a lower revision
   than what's already published — `prepare-packages.py` simply reuses the
   higher already-published revision in that case instead of downgrading
   (safe, but good to know).

3. **No changes needed to `src/deb/*/control` files** just to bump a
   revision — the upstream `Version:` field there should only change when
   the actual software version changes. The revision is purely a build-time
   parameter (CLI flag or workflow input), not something edited in the
   control files.
