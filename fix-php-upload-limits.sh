#!/bin/bash

# Fix PHP Upload Limits for Large Video Files
# Run with: sudo ./fix-php-upload-limits.sh

echo "🔧 Fixing PHP Upload Limits for Large Video Files..."

# PHP version detection
PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
echo "📋 PHP Version: $PHP_VERSION"

# PHP configuration paths
CLI_INI="/etc/php/$PHP_VERSION/cli/php.ini"
FPM_INI="/etc/php/$PHP_VERSION/fpm/php.ini"
APACHE_INI="/etc/php/$PHP_VERSION/apache2/php.ini"

# Settings to update for 2GB file support
declare -A SETTINGS=(
    ["post_max_size"]="4096M"
    ["upload_max_filesize"]="3072M"
    ["max_execution_time"]="7200"
    ["memory_limit"]="2048M"
    ["max_input_time"]="7200"
    ["max_input_vars"]="5000"
    ["default_socket_timeout"]="7200"
)

# Function to update PHP configuration
update_php_config() {
    local config_file=$1
    local config_name=$2
    
    if [ -f "$config_file" ]; then
        echo "📝 Updating $config_name configuration..."
        
        # Backup original file
        cp "$config_file" "$config_file.backup.$(date +%Y%m%d)"
        
        for setting in "${!SETTINGS[@]}"; do
            value=${SETTINGS[$setting]}
            
            # Check if setting exists and update it
            if grep -q "^$setting\s*=" "$config_file"; then
                sed -i "s|^$setting\s*=.*|$setting = $value|" "$config_file"
                echo "  ✅ Updated: $setting = $value"
            elif grep -q "^;\s*$setting\s*=" "$config_file"; then
                sed -i "s|^;\s*$setting\s*=.*|$setting = $value|" "$config_file"
                echo "  ✅ Enabled: $setting = $value"
            else
                echo "$setting = $value" >> "$config_file"
                echo "  ✅ Added: $setting = $value"
            fi
        done
        
        echo "  💾 Backup saved: $config_file.backup.$(date +%Y%m%d)"
    else
        echo "  ❌ Configuration file not found: $config_file"
    fi
}

# Update configurations
echo ""
echo "🔧 Updating PHP Configurations..."

# CLI configuration
update_php_config "$CLI_INI" "PHP CLI"

# FPM configuration
update_php_config "$FPM_INI" "PHP-FPM"

# Apache configuration (if exists)
update_php_config "$APACHE_INI" "PHP Apache"

echo ""
echo "🔄 Restarting Services..."

# Restart PHP-FPM
if systemctl is-active --quiet php$PHP_VERSION-fpm; then
    systemctl restart php$PHP_VERSION-fpm
    echo "  ✅ PHP-FPM restarted"
else
    echo "  ⚠️  PHP-FPM not running"
fi

# Restart Apache (if running)
if systemctl is-active --quiet apache2; then
    systemctl restart apache2
    echo "  ✅ Apache restarted"
elif systemctl is-active --quiet httpd; then
    systemctl restart httpd
    echo "  ✅ Apache (httpd) restarted"
fi

# Restart Nginx (if running)
if systemctl is-active --quiet nginx; then
    systemctl restart nginx
    echo "  ✅ Nginx restarted"
fi

echo ""
echo "📊 Current PHP Settings:"
php -r "
echo 'post_max_size: ' . ini_get('post_max_size') . PHP_EOL;
echo 'upload_max_filesize: ' . ini_get('upload_max_filesize') . PHP_EOL;
echo 'max_execution_time: ' . ini_get('max_execution_time') . PHP_EOL;
echo 'memory_limit: ' . ini_get('memory_limit') . PHP_EOL;
echo 'max_input_time: ' . ini_get('max_input_time') . PHP_EOL;
"

echo ""
echo "✅ PHP Upload Limits Fixed!"
echo ""
echo "📝 Notes:"
echo "1. Backup files created with .backup.$(date +%Y%m%d) extension"
echo "2. Test with your 20.74 MB file upload"
echo "3. Monitor logs: tail -f storage/logs/laravel.log"
echo ""
echo "🚀 Ready for large video uploads!"
