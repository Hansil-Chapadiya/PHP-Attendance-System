#!/bin/bash
# Production Deployment Script for InfinityFree

echo "📦 Preparing production deployment..."

# Create deployment directory
mkdir -p deploy

# Copy frontend files
echo "📋 Copying frontend files..."
cp -r frontend/* deploy/

# Copy backend files
echo "📋 Copying backend files..."
cp -r api deploy/api
cp -r backend deploy/backend

# Copy root files
cp index.php deploy/
cp .htaccess deploy/ 2>/dev/null || echo "⚠️  No .htaccess found (optional)"

# Clean up development files
echo "🧹 Cleaning development files..."
rm -f deploy/README.md
rm -f deploy/*.md

# Create production .htaccess
cat > deploy/.htaccess << 'EOF'
# Enable CORS
<IfModule mod_headers.c>
    Header set Access-Control-Allow-Origin "*"
    Header set Access-Control-Allow-Methods "GET, POST, OPTIONS"
    Header set Access-Control-Allow-Headers "Content-Type, Authorization"
</IfModule>

# Security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Prevent directory browsing
Options -Indexes

# Custom error pages
ErrorDocument 404 /index.html
ErrorDocument 500 /index.html

# PHP settings
<IfModule mod_php7.c>
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
    php_value max_execution_time 300
    php_value max_input_time 300
</IfModule>

# Redirect to HTTPS (if available)
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
EOF

# Create production index.php redirector
cat > deploy/index.php << 'EOF'
<?php
// Redirect to login page
header('Location: /frontend/login.html');
exit;
?>
EOF

echo "✅ Deployment package ready in ./deploy/"
echo ""
echo "📤 Upload Instructions:"
echo "1. Zip the deploy folder: cd deploy && zip -r ../deploy.zip ."
echo "2. Upload to InfinityFree via File Manager"
echo "3. Extract in htdocs/ directory"
echo "4. Verify config.php has correct credentials"
echo ""
echo "🌐 Access your site at: https://hcthegreat.ct.ws/"
