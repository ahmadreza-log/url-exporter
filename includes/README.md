# GitHub Updater

This directory contains the GitHub updater class for automatic plugin updates.

## How It Works

The `class-updater.php` file enables automatic updates from GitHub releases:

1. **Checks for Updates**: Queries GitHub API for the latest release
2. **Compares Versions**: Compares current version with latest release
3. **Shows Update Notice**: Displays update notification in WordPress
4. **Downloads Update**: Downloads and installs from GitHub release

## Features

- ✅ Automatic update notifications
- ✅ One-click updates from WordPress dashboard
- ✅ Changelog display from GitHub release notes
- ✅ Version comparison
- ✅ Caching to reduce API calls (12 hours)

## Configuration

The updater is configured in the main plugin file:

```php
// Initialize updater
if (is_admin()) {
    new URL_Exporter_Updater(__FILE__);
}
```

## GitHub Settings

- **Username**: `ahmadreza-log`
- **Repository**: `url-exporter`
- **API Endpoint**: `https://api.github.com/repos/ahmadreza-log/url-exporter/releases/latest`

## Creating Releases

To trigger an update:

1. Create a new tag: `git tag v1.1.1`
2. Push the tag: `git push origin v1.1.1`
3. GitHub Actions will automatically create the release
4. WordPress sites will detect the update within 12 hours

## Cache

Update checks are cached for 12 hours to avoid hitting GitHub API limits.
To force a check, clear the transient:

```php
delete_transient('url_exporter_release_info');
```

