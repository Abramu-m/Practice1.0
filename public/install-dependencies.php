<?php
/**
 * Install Dependencies on Bluehost
 * 
 * This script installs Composer dependencies for the DataTables package.
 * 
 * To use:
 * 1. Upload this file to your public folder on Bluehost
 * 2. Visit: https://your-domain.com/install-dependencies.php
 * 3. Delete this file after successful installation (for security)
 * 
 * Or run via SSH:
 * cd /home2/yyfcolmy/practice1.0/Practice1.0
 * /usr/local/bin/php /home2/yyfcolmy/practice1.0/Practice1.0/public/install-dependencies.php
 */

// Configuration
define('PROJECT_PATH', '/home2/yyfcolmy/practice1.0/Practice1.0');
define('COMPOSER_PATH', '/opt/cpanel/composer/bin/composer'); // Bluehost Composer path

// Simple authentication (change this password!)
define('INSTALL_PASSWORD', 'change_this_password_12345');

// Check password
if (!isset($_GET['password']) || $_GET['password'] !== INSTALL_PASSWORD) {
    http_response_code(403);
    die('Access denied. Add ?password=YOUR_PASSWORD to the URL.');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Install Dependencies</title>
    <style>
        body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px; }
        .log { background: #2d2d2d; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .success { color: #4ec9b0; }
        .error { color: #f48771; }
        .info { color: #569cd6; }
        h1 { color: #4ec9b0; }
        pre { margin: 0; }
    </style>
</head>
<body>
    <h1>📦 Installing Dependencies on Bluehost</h1>
    
    <?php
    // Change to project directory
    if (!chdir(PROJECT_PATH)) {
        echo '<div class="log error">❌ Error: Could not change to project directory: ' . PROJECT_PATH . '</div>';
        exit;
    }
    
    echo '<div class="log info">📂 Working directory: ' . getcwd() . '</div>';
    
    // Check if composer exists
    if (!file_exists(COMPOSER_PATH)) {
        echo '<div class="log error">❌ Error: Composer not found at: ' . COMPOSER_PATH . '</div>';
        echo '<div class="log info">💡 Try these alternative paths:</div>';
        echo '<div class="log"><pre>';
        echo '- /usr/local/bin/composer' . "\n";
        echo '- /usr/bin/composer' . "\n";
        echo '- composer (if in PATH)' . "\n";
        echo '</pre></div>';
        exit;
    }
    
    echo '<div class="log success">✓ Composer found at: ' . COMPOSER_PATH . '</div>';
    
    // Set HOME environment variable for Composer
    // Composer requires HOME to be set for its cache/config
    $homeDir = PROJECT_PATH . '/storage/composer-home';
    if (!is_dir($homeDir)) {
        mkdir($homeDir, 0755, true);
    }
    putenv('HOME=' . $homeDir);
    putenv('COMPOSER_HOME=' . $homeDir);
    
    echo '<div class="log info">🏠 Set COMPOSER_HOME: ' . $homeDir . '</div>';
    
    // Run composer install
    echo '<div class="log info">⚙️  Running: composer install --no-dev --optimize-autoloader</div>';
    echo '<div class="log"><pre>';
    
    $command = COMPOSER_PATH . ' install --no-dev --optimize-autoloader 2>&1';
    exec($command, $output, $returnCode);
    
    foreach ($output as $line) {
        echo htmlspecialchars($line) . "\n";
    }
    
    echo '</pre></div>';
    
    if ($returnCode === 0) {
        echo '<div class="log success">✅ Dependencies installed successfully!</div>';
        
        // Clear Laravel caches
        echo '<div class="log info">🧹 Clearing Laravel caches...</div>';
        
        $phpPath = '/usr/local/bin/php';
        $artisanCommands = [
            'config:clear',
            'cache:clear',
            'route:clear',
            'view:clear'
        ];
        
        foreach ($artisanCommands as $cmd) {
            echo '<div class="log"><pre>';
            echo "Running: php artisan {$cmd}\n";
            exec("{$phpPath} " . PROJECT_PATH . "/artisan {$cmd} 2>&1", $artisanOutput, $artisanReturn);
            foreach ($artisanOutput as $line) {
                echo htmlspecialchars($line) . "\n";
            }
            echo '</pre></div>';
            $artisanOutput = []; // Reset for next command
        }
        
        echo '<div class="log success">✅ All done! Your DataTables dependencies are installed.</div>';
        echo '<div class="log error">⚠️  IMPORTANT: Delete this file now for security!</div>';
        echo '<div class="log info">💡 You can delete it by visiting: <a href="?password=' . INSTALL_PASSWORD . '&delete=yes" style="color: #4ec9b0;">Click here to delete</a></div>';
        
    } else {
        echo '<div class="log error">❌ Installation failed with exit code: ' . $returnCode . '</div>';
        echo '<div class="log info">💡 You may need to run this via SSH instead.</div>';
    }
    
    // Self-delete option
    if (isset($_GET['delete']) && $_GET['delete'] === 'yes') {
        if (unlink(__FILE__)) {
            echo '<div class="log success">✅ This file has been deleted successfully!</div>';
        } else {
            echo '<div class="log error">❌ Could not delete this file. Please delete it manually via FTP/cPanel.</div>';
        }
    }
    ?>
    
    <div class="log info">
        <strong>📝 Manual Installation (via SSH):</strong>
        <pre>
ssh your-username@your-domain.com
cd /home2/yyfcolmy/practice1.0/Practice1.0
/opt/cpanel/composer/bin/composer install --no-dev --optimize-autoloader
/usr/local/bin/php artisan config:clear
/usr/local/bin/php artisan cache:clear
        </pre>
    </div>
</body>
</html>
