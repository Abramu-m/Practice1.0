<?php
/**
 * Cache clearing utility for Bluehost deployment
 * 
 * IMPORTANT: Delete this file after use for security reasons!
 * 
 * Usage: Visit yourdomain.com/clear-cache.php in your browser
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Cache Clear</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 50px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); max-width: 600px; margin: 0 auto; }
        .success { color: #28a745; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; margin: 10px 0; }
        .warning { color: #856404; padding: 10px; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 4px; margin: 10px 0; }
        h1 { color: #333; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Laravel Cache Clearer</h1>";

try {
    // Clear configuration cache
    $kernel->call('config:clear');
    echo "<div class='success'>✓ Configuration cache cleared successfully!</div>";
    
    // Clear application cache
    $kernel->call('cache:clear');
    echo "<div class='success'>✓ Application cache cleared successfully!</div>";
    
    // Clear route cache if exists
    if (file_exists(__DIR__.'/../bootstrap/cache/routes-v7.php')) {
        $kernel->call('route:clear');
        echo "<div class='success'>✓ Route cache cleared successfully!</div>";
    }
    
    // Clear view cache
    $kernel->call('view:clear');
    echo "<div class='success'>✓ View cache cleared successfully!</div>";
    
    echo "<div class='warning'>
        <strong>⚠ IMPORTANT:</strong> For security reasons, please delete this file (clear-cache.php) from your server immediately!
        <br><br>
        You can now test your NHIF integration. It should be using production mode.
    </div>";
    
} catch (Exception $e) {
    echo "<div class='warning'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "    </div>
</body>
</html>";
