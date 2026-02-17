# Installing DataTables Dependencies on Bluehost

DataTables is already loaded via CDN (no frontend installation needed), but you need to install the Laravel server-side package (`yajra/laravel-datatables-oracle`).

## ⚠️ Important Fix Applied

The installer script now automatically sets the `COMPOSER_HOME` environment variable, fixing the "HOME or COMPOSER_HOME environment variable must be set" error that occurs on shared hosting.

## Option 1: Web Installer (Recommended for Shared Hosting)

1. **Upload the installer script:**
   - Upload `public/install-dependencies.php` to your Bluehost public folder

2. **Change the password:**
   - Edit `install-dependencies.php` and change line 22:
     ```php
     define('INSTALL_PASSWORD', 'your_secure_password_here');
     ```

3. **Run the installer:**
   - Visit: `https://janet-healthcare.com/install-dependencies.php?password=your_secure_password_here`
   - The script will:
     - Set COMPOSER_HOME to `storage/composer-home`
     - Install composer dependencies
     - Clear Laravel caches
     - Display detailed logs

4. **Delete the file:**
   - Click the delete link shown in the script, OR
   - Manually delete via FTP/cPanel File Manager

## Option 2: SSH (If Available)

If you have SSH access enabled on Bluehost:

```bash
# Connect via SSH
ssh your-username@janet-healthcare.com

# Navigate to project
cd /home2/yyfcolmy/practice1.0/Practice1.0

# Set HOME for Composer and install
HOME=/home2/yyfcolmy/practice1.0/Practice1.0/storage/composer-home \
COMPOSER_HOME=/home2/yyfcolmy/practice1.0/Practice1.0/storage/composer-home \
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

## Alternative: Upload Vendor Folder (Last Resort)

If you cannot run Composer on Bluehost at all, you can install dependencies locally and upload the vendor folder:

1. **On your local machine:**
   ```bash
   cd C:\xampp\htdocs\Practice1.0
   composer install --no-dev --optimize-autoloader
   ```

2. **Upload to Bluehost:**
   - Zip the `vendor` folder (this will be large, ~50-100MB)
   - Upload via FTP or cPanel File Manager
   - Extract on the server
   - Ensure the path is: `/home2/yyfcolmy/practice1.0/Practice1.0/vendor`

3. **Set permissions:**
   - Make sure the vendor folder is readable (755 permissions)

**Note:** This method is not ideal because:
- The vendor folder is large (slow to upload)
- You'll need to re-upload every time composer.json changes
- The webhook won't be able to auto-update dependencies

## Enabling SSH on Bluehost

If you don't have SSH access but want it:

1. Log into cPanel
2. Go to **Security** → **SSH Access**
3. Click **Manage SSH Keys**
4. Generate a new key pair or upload your public key
5. Authorize the key
6. Use the key to connect via SSH

SSH is the most reliable way to manage Composer dependencies on shared hosting.

## Security Note

**Always delete `install-dependencies.php` after use!** This file can execute commands on your server and should not be left accessible.
