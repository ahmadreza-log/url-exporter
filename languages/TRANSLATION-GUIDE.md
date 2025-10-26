# Translation Quick Fix Guide

## Problem: Translations Not Loading

If translations are not showing up, follow these steps:

### Step 1: Complete the Translation

1. Open **Poedit** (Download: https://poedit.net/)
2. Open file: `url-exporter-fa_IR.po`
3. Translate the empty string:
   - Original: "Export URLs"
   - Translation: "دریافت آدرس‌ها"
4. Click **Save** (Poedit will auto-generate the .mo file)

### Step 2: Verify Files Exist

Check that these files are in the `languages/` folder:
```
✅ url-exporter-fa_IR.po  (source file)
✅ url-exporter-fa_IR.mo  (compiled file)
```

### Step 3: Check WordPress Locale

Make sure WordPress is set to Persian:
1. Go to: **Settings → General**
2. **Site Language**: فارسی (Persian)
3. Save and refresh

### Step 4: Clear Caches

Clear all caches:
```php
// Add to wp-config.php temporarily
define('WP_CACHE', false);

// Or use a plugin like WP Super Cache → Delete Cache
```

### Step 5: Force Load Translation

If still not working, add this to your theme's `functions.php`:

```php
// Force load URL Exporter translation
add_action('plugins_loaded', function() {
    $locale = get_locale();
    $mofile = WP_PLUGIN_DIR . '/url-exporter/languages/url-exporter-' . $locale . '.mo';
    if (file_exists($mofile)) {
        load_textdomain('url-exporter', $mofile);
    }
}, 1);
```

### Step 6: Debug

Check if translation is loaded:

```php
// Add this to test
add_action('admin_notices', function() {
    if (is_textdomain_loaded('url-exporter')) {
        echo '<div class="notice notice-success"><p>✅ Translation loaded!</p></div>';
        echo '<div class="notice notice-info"><p>Test: ' . __('Export URLs', 'url-exporter') . '</p></div>';
    } else {
        echo '<div class="notice notice-error"><p>❌ Translation NOT loaded</p></div>';
    }
});
```

### Alternative: Use Loco Translate Plugin

1. Install **Loco Translate** plugin
2. Go to: **Loco Translate → Plugins → URL Exporter**
3. Edit Persian translation
4. Save

### Common Issues

**Issue 1: .mo file not generated**
- Solution: Save the .po file in Poedit (it auto-generates .mo)

**Issue 2: Wrong file name**
- Must be: `url-exporter-fa_IR.mo` (not `url-exporter-fa.mo`)

**Issue 3: Wrong WordPress locale**
- Check: `get_locale()` should return `fa_IR`

**Issue 4: Cached translations**
- Clear all caches (browser, plugin, object cache)

### Need Help?

1. Check file permissions (should be readable)
2. Verify file encoding is UTF-8
3. Make sure text-domain matches: `url-exporter`
4. Open an issue on GitHub

---

**Quick Test Command:**

Add to any PHP file and visit it:
```php
<?php
require_once('../../../wp-load.php');
echo 'Locale: ' . get_locale() . '<br>';
echo 'Translation: ' . __('Export URLs', 'url-exporter');
```

