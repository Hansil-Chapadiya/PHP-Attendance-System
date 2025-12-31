# InfinityFree Cache Clear Kaise Kare

## Problem
Files update nahi ho rahi - purani files show ho rahi hain even after upload/delete

## Solutions (Try in Order)

### Method 1: .htaccess se Cache Disable karo
Upload this `.htaccess` file to your `htdocs` folder:

```apache
# Disable caching completely
<IfModule mod_headers.c>
    Header set Cache-Control "no-cache, no-store, must-revalidate"
    Header set Pragma "no-cache"
    Header set Expires 0
</IfModule>

# For PHP files specifically
<FilesMatch "\.(php)$">
    Header set Cache-Control "no-cache, no-store, must-revalidate"
    Header set Pragma "no-cache"
    Header set Expires 0
</FilesMatch>

# Force latest version
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
</IfModule>
```

### Method 2: File ko rename karke upload karo
Instead of `index.php`, upload as `index2.php` temporarily, then:
1. Delete old `index.php`
2. Wait 2-3 minutes
3. Rename `index2.php` back to `index.php`

### Method 3: Version Number add karo
Add timestamp/version to trigger cache clear:

```php
<?php
// Version: 2024-12-31-15-30 (change ye timestamp)
header('Location: /frontend/login.html');
exit;
?>
```

### Method 4: Browser Cache Clear karo
- Hard Refresh: `Ctrl + Shift + R` (Chrome/Firefox)
- Or Incognito/Private window mein test karo

### Method 5: InfinityFree Control Panel se
1. Login to InfinityFree
2. Go to "Control Panel"
3. Look for "Clear Cache" or "Cloudflare Cache" option
4. Purge entire cache

### Method 6: Wait kar (Last Resort)
InfinityFree cache automatically clear hota hai 24-48 hours mein

## Best Practice for Future
Always upload with `.htaccess` that disables caching during development
