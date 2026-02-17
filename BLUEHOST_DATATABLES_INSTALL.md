# Installing DataTables Dependencies on Bluehost

DataTables is already loaded via CDN (no frontend installation needed), but you need to install the Laravel server-side package (`yajra/laravel-datatables-oracle`).

## Option 1: Automatic (One-time Setup)

1. **Upload the installer script:**
   - Upload `public/install-dependencies.php` to your Bluehost public folder

2. **Change the password:**
   - Edit `install-dependencies.php` and change line 17:
     ```php
     define('INSTALL_PASSWORD', 'your_secure_password_here');
     ```

3. **Run the installer:**
   - Visit: `https://janet-healthcare.com/install-dependencies.php?password=your_secure_password_here`
   - The script will:
     - Install composer dependencies
     - Clear Laravel caches
     - Display detailed logs

4. **Delete the file:**
   - Click the delete link shown in the script, OR
   - Manually delete via FTP/cPanel File Manager

## Option 2: SSH (Recommended for recurring updates)

```bash
# Connect via SSH
ssh your-username@janet-healthcare.com

# Navigate to project
cd /home2/yyfcolmy/practice1.0/Practice1.0

# Install dependencies
/opt/cpanel/composer/bin/composer install --no-dev --optimize-autoloader

# Clear caches
/usr/local/bin/php artisan config:clear
/usr/local/bin/php artisan cache:clear
/usr/local/bin/php artisan route:clear
/usr/local/bin/php artisan view:clear
```

## Option 3: Automatic via Webhook (Already Configured)

The webhook (`public/webhook.php`) now automatically runs `composer install` on every GitHub push, so dependencies will be installed automatically going forward!

## Verification

After installation, check that DataTables is working:
1. Visit: `https://janet-healthcare.com/patients`
2. You should see a search box, pagination, and sortable columns
3. Check browser console for any errors (F12)

## Troubleshooting

### If Composer path is wrong:
The default path is `/opt/cpanel/composer/bin/composer`. If this doesn't work, try:
- `/usr/local/bin/composer`
- `/usr/bin/composer`
- Or just `composer` (if in PATH)

Update the path in both:
- `public/install-dependencies.php` (line 16)
- `public/webhook.php` (line 75)

### If you see "Class not found" errors:
Run this via SSH:
```bash
cd /home2/yyfcolmy/practice1.0/Practice1.0
/opt/cpanel/composer/bin/composer dump-autoload
/usr/local/bin/php artisan config:clear
```

### Check installed packages:
```bash
cd /home2/yyfcolmy/practice1.0/Practice1.0
/opt/cpanel/composer/bin/composer show | grep datatables
```

You should see: `yajra/laravel-datatables-oracle`

## Security Note

**Always delete `install-dependencies.php` after use!** This file can execute commands on your server and should not be left accessible.
