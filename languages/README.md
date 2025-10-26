# Translation Guide

This directory contains translation files for the URL Exporter plugin.

## Files

- **url-exporter.pot** - Template file for all translatable strings
- **url-exporter-{locale}.po** - Translation file for specific language
- **url-exporter-{locale}.mo** - Compiled translation file

## How to Translate

### Using Poedit

1. **Download and Install Poedit**
   - Download from: https://poedit.net/
   - Install on your system

2. **Open the POT file**
   - Open Poedit
   - Click "Create New Translation"
   - Select `url-exporter.pot`
   - Choose your language (e.g., Persian/Farsi - fa_IR)

3. **Translate Strings**
   - Translate each string in the list
   - Save the file as `url-exporter-{locale}.po`
   - Example: `url-exporter-fa_IR.po` for Persian
   - Poedit will automatically generate the `.mo` file

4. **Install Translation**
   - Place both `.po` and `.mo` files in this `languages/` directory
   - WordPress will automatically load the correct translation

### Example Languages

**Persian (فارسی):**
```
File: url-exporter-fa_IR.po
File: url-exporter-fa_IR.mo
```

**Arabic (العربية):**
```
File: url-exporter-ar.po
File: url-exporter-ar.mo
```

**German (Deutsch):**
```
File: url-exporter-de_DE.po
File: url-exporter-de_DE.mo
```

## Translatable Strings

The plugin contains the following translatable strings:

- "Export URLs" - Link text in taxonomy list
- "Loading URLs..." - Loading message
- "Error loading URLs" - Error message
- "No posts found" - Empty state message
- "URL List" - Modal title
- "URL" - Table column header
- Permission and validation error messages

## Contributing Translations

If you've created a translation:

1. Test it in your WordPress installation
2. Submit a Pull Request on GitHub with your `.po` and `.mo` files
3. Include your name in the translation credits

## Language Codes

Common WordPress language codes:

- English (US): `en_US`
- Persian: `fa_IR`
- Arabic: `ar`
- French: `fr_FR`
- German: `de_DE`
- Spanish: `es_ES`
- Italian: `it_IT`
- Portuguese (Brazil): `pt_BR`
- Russian: `ru_RU`
- Chinese (Simplified): `zh_CN`
- Japanese: `ja`

## Need Help?

- Poedit Documentation: https://poedit.net/trac/wiki/Doc
- WordPress i18n Guide: https://developer.wordpress.org/apis/internationalization/
- Open an issue: https://github.com/ahmadreza-log/url-exporter/issues

## Credits

- Plugin Author: Ahmadreza Ebrahimi
- Original Language: English
- Translation Contributors: (Your name here!)

