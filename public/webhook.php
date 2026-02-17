<?php
/**
 * GitHub Webhook for Auto-Deployment
 * URL: https://janet-healthcare.com/webhook.php
 */

// Bootstrap Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Log;

// Configuration
define('SECRET', 'my_secure_webhook_secret_2026'); // Match this with GitHub webhook secret
define('PROJECT_PATH', '/home2/yyfcolmy/practice1.0/Practice1.0'); // Update with your actual Bluehost path
define('BRANCH', 'master'); // or 'main' depending on your branch name

// Function to verify GitHub signature
function verifyGitHubSignature($payload, $signature) {
    if (empty($signature)) {
        return false;
    }
    
    $hash = 'sha256=' . hash_hmac('sha256', $payload, SECRET);
    return hash_equals($hash, $signature);
}

// Start deployment
Log::channel('webhook')->info('==========================================================');
Log::channel('webhook')->info('🚀 DEPLOYMENT STARTED');
Log::channel('webhook')->info('==========================================================');

// Get payload
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

// Verify signature
if (!verifyGitHubSignature($payload, $signature)) {
    Log::channel('webhook')->error('Invalid signature - deployment rejected');
    Log::channel('webhook')->info('==========================================================');
    http_response_code(403);
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

Log::channel('webhook')->info('✓ Signature verified successfully');

// Decode payload
$data = json_decode($payload, true);
$pusherName = $data['pusher']['name'] ?? 'Unknown';
$commitMessage = $data['head_commit']['message'] ?? 'No message';
$branch = $data['ref'] ?? 'refs/heads/' . BRANCH;
$branch = str_replace('refs/heads/', '', $branch);

Log::channel('webhook')->info('📝 Deployment Info', [
    'pusher' => $pusherName,
    'branch' => $branch,
    'commit' => $commitMessage
]);
Log::channel('webhook')->info('----------------------------------------------------------');

// Change to project directory and execute deployment commands
chdir(PROJECT_PATH);

// Define full paths
$sshKey = '/home2/yyfcolmy/.ssh/github_deploy_3';
$sshCommand = 'ssh -i ' . $sshKey . ' -o IdentitiesOnly=yes -o StrictHostKeyChecking=accept-new';
$gitCommand = 'cd ' . PROJECT_PATH . ' && GIT_SSH_COMMAND=' . escapeshellarg($sshCommand) . ' git pull origin ' . BRANCH . ' 2>&1';
$phpPath = '/usr/local/bin/php'; // Bluehost PHP path, adjust if needed

$commands = [
    $gitCommand,
    'cd ' . PROJECT_PATH . ' && HOME=' . PROJECT_PATH . '/storage/composer-home COMPOSER_HOME=' . PROJECT_PATH . '/storage/composer-home /opt/cpanel/composer/bin/composer install --no-dev --optimize-autoloader 2>&1',
    $phpPath . ' ' . PROJECT_PATH . '/artisan config:clear 2>&1',
    $phpPath . ' ' . PROJECT_PATH . '/artisan cache:clear 2>&1',
    $phpPath . ' ' . PROJECT_PATH . '/artisan route:clear 2>&1',
    $phpPath . ' ' . PROJECT_PATH . '/artisan view:clear 2>&1',
    $phpPath . ' ' . PROJECT_PATH . '/artisan config:cache 2>&1',
    $phpPath . ' ' . PROJECT_PATH . '/artisan route:cache 2>&1',
    $phpPath . ' ' . PROJECT_PATH . '/artisan view:cache 2>&1',
    // Comment if you dont want automatic migrations
    $phpPath . ' ' . PROJECT_PATH . '/artisan migrate --force 2>&1',
];

$results = [];
$allSuccess = true;
$stepNumber = 1;

Log::channel('webhook')->info('⚙️  Executing deployment commands');

foreach ($commands as $command) {
    // Extract command name for cleaner display
    $commandName = 'Unknown';
    if (strpos($command, 'git pumposer install') !== false) $commandName = 'Install Dependencies';
    elseif (strpos($command, 'coll') !== false) $commandName = 'Git Pull';
    elseif (strpos($command, 'config:clear') !== false) $commandName = 'Clear Config Cache';
    elseif (strpos($command, 'cache:clear') !== false) $commandName = 'Clear App Cache';
    elseif (strpos($command, 'route:clear') !== false) $commandName = 'Clear Route Cache';
    elseif (strpos($command, 'view:clear') !== false) $commandName = 'Clear View Cache';
    elseif (strpos($command, 'config:cache') !== false) $commandName = 'Cache Config';
    elseif (strpos($command, 'route:cache') !== false) $commandName = 'Cache Routes';
    elseif (strpos($command, 'view:cache') !== false) $commandName = 'Cache Views';
    elseif (strpos($command, 'migrate') !== false) $commandName = 'Run Migrations';
    
    exec($command, $output, $returnCode);
    
    $commandOutput = implode("\n", $output);
    
    if ($returnCode === 0) {
        Log::channel('webhook')->info("✓ Step {$stepNumber}: {$commandName} - Success", [
            'exit_code' => $returnCode,
            'output' => !empty($commandOutput) ? array_slice(explode("\n", trim($commandOutput)), -3) : []
        ]);
    } else {
        Log::channel('webhook')->error("❌ Step {$stepNumber}: {$commandName} - Failed", [
            'exit_code' => $returnCode,
            'output' => !empty($commandOutput) ? explode("\n", trim($commandOutput)) : []
        ]);
        $allSuccess = false;
    }
    
    $results[] = [
        'step' => $stepNumber,
        'name' => $commandName,
        'command' => $command,
        'output' => $commandOutput,
        'success' => $returnCode === 0
    ];
    
    $output = []; // Clear for next command
    $stepNumber++;
}

Log::channel('webhook')->info('----------------------------------------------------------');

// Summary
$successCount = count(array_filter($results, fn($r) => $r['success']));
$totalCount = count($results);

$failedSteps = array_filter($results, fn($r) => !$r['success']);

if ($allSuccess) {
    Log::channel('webhook')->info('✅ DEPLOYMENT SUCCESSFUL', [
        'total_steps' => $totalCount,
        'successful_steps' => $successCount,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} else {
    Log::channel('webhook')->warning('⚠️  DEPLOYMENT COMPLETED WITH ERRORS', [
        'total_steps' => $totalCount,
        'successful_steps' => $successCount,
        'failed_steps' => $totalCount - $successCount,
        'failed_step_names' => array_map(fn($r) => "Step {$r['step']}: {$r['name']}", array_values($failedSteps)),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

Log::channel('webhook')->info('==========================================================');
Log::channel('webhook')->info(''); // Extra spacing between deployments

// Return response
http_response_code($allSuccess ? 200 : 500);
echo json_encode([
    'status' => $allSuccess ? 'success' : 'partial_failure',
    'pusher' => $pusherName,
    'branch' => $branch,
    'commit' => $commitMessage,
    'summary' => [
        'total_steps' => $totalCount,
        'successful_steps' => $successCount,
        'failed_steps' => $totalCount - $successCount
    ],
    'results' => $results,
    'timestamp' => date('Y-m-d H:i:s')
]);
