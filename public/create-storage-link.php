<?php
/**
 * Temporary Storage Link Creator
 * 
 * This file creates a symbolic link from public/storage to storage/app/public
 * Access this file once via browser: yourdomain.com/create-storage-link.php
 * 
 * DELETE THIS FILE after running it for security reasons!
 */

// Security: Only allow execution if not already linked
if (file_exists(__DIR__ . '/storage')) {
    die('<h2>✓ Storage link already exists!</h2><p>The symbolic link is already created. Please delete this file for security.</p>');
}

// Get the paths
$target = dirname(__DIR__) . '/storage/app/public';
$link = __DIR__ . '/storage';

// Check if target directory exists
if (!is_dir($target)) {
    die('<h2>✗ Error</h2><p>Storage directory not found at: ' . $target . '</p>');
}

// Try to create the symbolic link
try {
    // For Windows
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Use junction on Windows
        $command = 'mklink /J "' . $link . '" "' . $target . '"';
        exec($command, $output, $return);
        
        if ($return === 0 || file_exists($link)) {
            echo '<h2>✓ Success!</h2>';
            echo '<p>Storage link created successfully using Windows junction.</p>';
            echo '<p><strong>Target:</strong> ' . $target . '</p>';
            echo '<p><strong>Link:</strong> ' . $link . '</p>';
            echo '<hr><p style="color: red;"><strong>IMPORTANT: Please delete this file (create-storage-link.php) immediately for security!</strong></p>';
        } else {
            echo '<h2>✗ Failed</h2>';
            echo '<p>Could not create junction. Output: ' . implode('<br>', $output) . '</p>';
            echo '<p>You may need to run this with administrator privileges.</p>';
        }
    } else {
        // For Linux/Unix
        if (symlink($target, $link)) {
            echo '<h2>✓ Success!</h2>';
            echo '<p>Storage link created successfully.</p>';
            echo '<p><strong>Target:</strong> ' . $target . '</p>';
            echo '<p><strong>Link:</strong> ' . $link . '</p>';
            echo '<hr><p style="color: red;"><strong>IMPORTANT: Please delete this file (create-storage-link.php) immediately for security!</strong></p>';
        } else {
            echo '<h2>✗ Failed</h2>';
            echo '<p>Could not create symbolic link. You may need to:</p>';
            echo '<ul>';
            echo '<li>Check directory permissions</li>';
            echo '<li>Contact your hosting provider</li>';
            echo '<li>Run: php artisan storage:link via SSH</li>';
            echo '</ul>';
        }
    }
} catch (Exception $e) {
    echo '<h2>✗ Error</h2>';
    echo '<p>Exception: ' . $e->getMessage() . '</p>';
}

// Add some styling
echo '<style>
    body { 
        font-family: Arial, sans-serif; 
        max-width: 800px; 
        margin: 50px auto; 
        padding: 20px;
        background: #f5f5f5;
    }
    h2 { 
        color: #333; 
        border-bottom: 2px solid #007bff;
        padding-bottom: 10px;
    }
    p, ul { 
        line-height: 1.6; 
        color: #666;
    }
</style>';
?>
