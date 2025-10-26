# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2025-10-26

### Added

- Object-oriented architecture (OOP) with `URL_Exporter` class
- Nonce system for AJAX security
- User capability checks (`capability check`)
- Complete sanitization and validation for all inputs
- Display post titles in table
- Display post publish dates
- Individual copy button for each URL
- "Copy All" button to copy all URLs at once
- Close button (×) in modal header
- Modal footer with action buttons
- ESC key support to close modal
- XSS Prevention in JavaScript with `escapeHtml` function
- Clipboard API support with fallback for older browsers
- Smooth fadeIn animation for modal opening
- Responsive design for mobile devices
- Full RTL support
- Loading spinner for loading state
- Clear error message display
- Display category information and post count
- Conditional asset loading (only on taxonomy pages)
- Helper functions for truncating long URLs
- Complete code documentation with PHPDoc and JSDoc
- English README.md with complete documentation
- CHANGELOG.md for tracking changes
- .gitignore file for the project
- **Batch processing system for large datasets**
- **Real-time progress bar with percentage**
- **Timeout management (30s for count, 60s per batch)**
- **WP_Query optimization for better performance**
- **Error recovery - continues even if some batches fail**
- **Support for 700+ posts without server timeout**

### Changed

- Complete restructuring of PHP file from anonymous functions to class
- Improved modal HTML structure with separate header and footer
- Enhanced and extended CSS styles
- Improved JavaScript code to modular architecture
- Changed from nested CSS to standard CSS for better compatibility
- Enhanced table using WordPress classes
- Increased modal width to 900px for better display
- Improved z-index to prevent overlap with other elements
- Enhanced AJAX response with more information
- Changed export link selector from `.export-urls a` to `.url-exporter-trigger`
- Added data attributes to links for easier access
- Optimized query with `WP_Query` instead of `get_posts`
- Added `set_time_limit(300)` for large datasets
- Split requests into batches of 50 posts each

### Security

- Added `wp_create_nonce` for nonce generation
- Added `check_ajax_referer` for nonce verification
- Added `current_user_can` for permission check
- Used `sanitize_text_field` for input sanitization
- Used `absint` for integer sanitization of IDs
- Used `taxonomy_exists` to check taxonomy existence
- Used `get_term` to check term existence
- Used `esc_html__`, `esc_attr`, `esc_url` for safe output
- Protection against XSS in JavaScript

### Performance

- Conditional asset loading only on taxonomy pages
- Using version constant for cache busting
- Reduced number of DOM operations
- Using event delegation for better performance
- Optimized post queries with `post_status` limitation
- Cleaner and more maintainable code
- **Batch processing prevents timeout on large datasets**
- **Progressive loading reduces memory usage**
- **Optimized queries with `fields` and `no_found_rows` parameters**
- **Maximum 100 posts per batch to prevent abuse**

### Fixed

- Server timeout on large datasets (700+ posts)
- Memory issues with bulk exports
- Better error handling and recovery
- Progress tracking for user feedback

## [1.0.0] - 2024

### Added

- Initial plugin release
- Extract post URLs based on taxonomy
- Simple modal display
- AJAX request to fetch URLs
- Simple table display of URLs

[1.1.0]: https://github.com/ahmadreza-log/url-exporter/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/ahmadreza-log/url-exporter/releases/tag/v1.0.0
