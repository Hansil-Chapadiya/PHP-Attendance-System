#!/bin/bash
# Production Deployment Script

echo "========================================="
echo "  Production Deployment Preparation"
echo "========================================="
echo ""

# 1. Remove test files
echo "1. Removing test and development files..."
rm -f test_*.php
rm -f verify_*.php
rm -f quick_test.php
rm -f check_*.php
rm -f fix_*.php
rm -f create_rate_limit_table.php
echo "   ✓ Test files removed"
echo ""

# 2. Check .env file
echo "2. Checking environment configuration..."
if [ ! -f ".env" ]; then
    echo "   ❌ ERROR: .env file not found!"
    echo "   Copy .env.example to .env and configure it"
    exit 1
else
    echo "   ✓ .env file exists"
fi
echo ""

# 3. Verify .gitignore
echo "3. Checking .gitignore..."
if grep -q "backend/config.php" .gitignore && grep -q ".env" .gitignore; then
    echo "   ✓ Sensitive files are in .gitignore"
else
    echo "   ⚠ WARNING: Update .gitignore to include sensitive files"
fi
echo ""

# 4. Set file permissions
echo "4. Setting secure file permissions..."
chmod 600 .env 2>/dev/null
chmod 640 backend/config.php 2>/dev/null
chmod 644 backend/*.sql 2>/dev/null
echo "   ✓ Permissions set"
echo ""

# 5. Check PHP configuration
echo "5. Checking PHP configuration..."
php -r "if (ini_get('display_errors') == '1') echo '   ⚠ WARNING: display_errors is ON\n'; else echo '   ✓ display_errors is OFF\n';"
echo ""

# 6. Generate JWT secret if needed
echo "6. JWT Secret Key..."
if command -v openssl &> /dev/null; then
    echo "   Generate a new secret with:"
    echo "   openssl rand -base64 64"
    echo ""
else
    echo "   ⚠ OpenSSL not found. Install it to generate secure secrets."
fi

echo "========================================="
echo "  Next Steps:"
echo "========================================="
echo "1. Configure .env with production values"
echo "2. Run: mysql < backend/setup_production_db.sql"
echo "3. Update CORS origins in backend/helpers.php"
echo "4. Test on staging environment"
echo "5. Set up HTTPS certificate"
echo "6. Configure error logging"
echo "7. Set up database backups"
echo ""
echo "See PRODUCTION_READINESS.md for complete checklist"
echo "========================================="
