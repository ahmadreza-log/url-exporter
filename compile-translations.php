<?php
/**
 * Compile PO to MO file
 * Run this after editing .po files, then delete it
 */

// Simple PO to MO compiler
function po_to_mo($po_file, $mo_file) {
    $po_content = file_get_contents($po_file);
    
    // This is a simplified version - for production, use Poedit or gettext tools
    // For now, just copy the existing .mo file or use Poedit
    
    echo "⚠️  Please use Poedit to compile translations:\n\n";
    echo "1. Open Poedit\n";
    echo "2. Open: {$po_file}\n";
    echo "3. Make sure 'Export URLs' is translated to: دریافت آدرس‌ها\n";
    echo "4. Click 'Save' - Poedit will automatically create the .mo file\n";
    echo "5. Refresh your WordPress admin page\n\n";
    echo "✅ Or download Poedit from: https://poedit.net/\n";
}

$po_file = __DIR__ . '/languages/url-exporter-fa_IR.po';
$mo_file = __DIR__ . '/languages/url-exporter-fa_IR.mo';

po_to_mo($po_file, $mo_file);

