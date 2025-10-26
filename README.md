# URL Exporter - WordPress Plugin

![Version](https://img.shields.io/badge/version-1.1.0-blue)
![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-green)
![PHP](https://img.shields.io/badge/PHP-7.2%2B-purple)
![License](https://img.shields.io/badge/license-GPL--2.0-orange)

A powerful WordPress plugin for exporting and managing URLs of posts based on categories and tags.

## ✨ Features in Version 1.1.0

### 🔒 Enhanced Security

- ✅ **Nonce Verification**: Security checks added for all AJAX requests
- ✅ **Capability Check**: User access control (administrators only)
- ✅ **Input Sanitization**: Complete sanitization and validation of all inputs
- ✅ **XSS Prevention**: Protection against Cross-Site Scripting attacks
- ✅ **Data Validation**: Verification of all data before processing

### 🎨 Improved User Interface

- 📊 **Professional Table Display**: Table with columns for title, URL, date, and actions
- 🎯 **Copy Button for Each URL**: Quick copy for individual URLs
- 📋 **Copy All URLs**: Button to copy all URLs at once
- ⌨️ **ESC to Close**: Ability to close modal with Escape key
- ✨ **Smooth Animations**: Beautiful and fluid visual effects
- 📱 **Responsive Design**: Compatible with mobile and tablet devices
- 🌐 **RTL Support**: Compatible with right-to-left languages

### 🚀 Optimized Performance

- ⚡ **Class-Based Architecture**: Object-oriented and modular architecture
- 🎯 **Conditional Loading**: Loading assets only on required pages
- 🧩 **Modular JavaScript**: Modular and maintainable JavaScript code
- 📦 **Request Optimization**: Reduced server load
- 🔄 **Error Handling**: Professional error management
- 🔥 **Batch Processing**: Handle large datasets without timeout (700+ posts)
- 📊 **Progress Bar**: Real-time loading progress for large exports

### 📊 Additional Information

- 📈 **Display Post Count**: Complete information in modal
- 📅 **Display Publish Date**: Date of each post in table
- 🏷️ **Display Category Name**: Identification of selected category

## 📋 Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- jQuery (included in WordPress)

## 🔧 Installation

1. Place the plugin files in `wp-content/plugins/url-exporter/`
2. Go to the Plugins section in WordPress admin panel
3. Activate the "URL Exporter" plugin

## 🎯 How to Use

### Exporting URLs from a Category or Tag

1. Go to the category or tag management page
2. Click on the **"Export URLs"** link
3. A modal will display with the list of all URLs

### Available Operations

- 👁️ **View List**: View all posts and their URLs
- 📋 **Copy URL**: Copy each URL individually
- 📑 **Copy All**: Copy all URLs at once
- 🔗 **Open Link**: Open URL in new tab

## 📂 File Structure

```
url-exporter/
├── url-exporter.php          # Main plugin file (PHP class)
├── assets/
│   ├── js/
│   │   └── script.js         # Modular and optimized JavaScript
│   └── css/
│       └── style.css         # Complete and professional styles
├── README.md                 # Documentation (this file)
├── CHANGELOG.md              # Change history
└── .gitignore                # Git ignore file
```

## 🔐 Security

### Security Measures Implemented

1. **Nonce Verification**

```php
check_ajax_referer('url_exporter_nonce', 'nonce');
```

2. **Capability Check**

```php
if (!current_user_can('manage_options')) { ... }
```

3. **Data Sanitization**

```php
$taxonomy = sanitize_text_field($_GET['taxonomy']);
$term_id  = absint($_GET['ID']);
```

4. **Output Escaping**

```php
esc_html__(), esc_attr(), esc_url()
```

5. **XSS Prevention in JavaScript**

```javascript
escapeHtml: function(text) { ... }
```

## 🚀 Batch Processing for Large Datasets

The plugin handles large datasets efficiently:

- **Automatic Batching**: Processes 50 posts per request
- **Progress Tracking**: Real-time progress bar for exports over 50 posts
- **No Timeout**: Can handle 700+ posts without server timeout
- **Error Recovery**: Continues even if some batches fail

### Performance Example

For 700 posts:
- Splits into 14 batches of 50 posts each
- Total time: ~30 seconds
- No server timeout or memory issues

## 🎨 CSS Customization

You can modify the plugin's styles by adding custom CSS to your theme:

```css
/* Change modal primary color */
.url-exporter-modal-header {
    background-color: #your-color;
}

/* Change modal width */
.url-exporter-modal-content {
    max-width: 1200px;
}
```

## 🛠️ Development and Debugging

To enable development mode:

```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## 📝 Changelog

### Version 1.1.0 (Current)

- ✨ Complete object-oriented architecture (OOP)
- 🔒 Enhanced security with Nonce and Capability Check
- 🎨 Completely redesigned user interface
- 📋 Added URL copy functionality
- 📱 Responsive and mobile-first design
- 🚀 Performance and speed optimization
- 🧩 Modular and maintainable code
- 📊 Display more information (title, date)
- ⌨️ Keyboard support (ESC)
- 🌐 Full RTL support
- 🔥 Batch processing for large datasets
- 📊 Progress bar for large exports

### Version 1.0.0

- 🎉 Initial release
- 📋 Basic URL export functionality

## 👨‍💻 Developer

**Ahmadreza Ebrahimi**

- 🌐 Website: [ahmadreza.me](https://ahmadreza.me)
- 💻 GitHub: [ahmadreza-log/url-exporter](https://github.com/ahmadreza-log/url-exporter)

## 📄 License

This plugin is released under the GPL v2 or later license.

## 🤝 Contributing

To report bugs or suggestions

- Through GitHub Issues
- Submit Pull Request

## 📞 Support

If you need support

1. First read the documentation
2. Post your issue on GitHub Issues
3. Contact the developer

---

**Made with ❤️ for the WordPress Community**
