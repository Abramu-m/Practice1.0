<?php
/**
 * GitHub Webhook for Auto-Deployment
 * URL: https://janet-healthcare.com/webhook.php
 */

// Configuration
define('SECRET', 'my_secure_webhook_secret_2026'); // Match this with GitHub webhook secret
define('PROJECT_PATH', '/home2/yyfcolmy/practice1.0/Practice1.0'); // Update with your actual Bluehost path
define('LOG_FILE', PROJECT_PATH . '/storage/logs/webhook.log');
define('BRANCH', 'master'); // or 'main' depending on your branch name

// Function to log messages
function logMessage($message, $indent = 0) {
    $timestamp = date('Y-m-d H:i:s');
    $indentation = str_repeat('  ', $indent);
    $logEntry = "[{$timestamp}] {$indentation}{$message}\n";
    file_put_contents(LOG_FILE, $logEntry, FILE_APPEND);
    echo $logEntry;
}

// Function to log separator
function logSeparator($char = '=', $length = 80) {
    $line = str_repeat($char, $length) . "\n";
    file_put_contents(LOG_FILE, $line, FILE_APPEND);
    echo $line;
}

// Function to verify GitHub signature
function verifyGitHubSignature($payload, $signature) {
    if (empty($signature)) {
        return false;
    }
    
    $hash = 'sha256=' . hash_hmac('sha256', $payload, SECRET);
    return hash_equals($hash, $signature);
}

// Start
logSeparator();
logMessage("🚀 DEPLOYMENT STARTED");
logSeparator();

// Get payload
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

// Verify signature
if (!verifyGitHubSignature($payload, $signature)) {
    logMessage("❌ ERROR: Invalid signature", 1);
    logSeparator();
    http_response_code(403);
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

logMessage("✓ Signature verified successfully", 1);

// Decode payload
$data = json_decode($payload, true);
$pusherName = $data['pusher']['name'] ?? 'Unknown';
$commitMessage = $data['head_commit']['message'] ?? 'No message';
$branch = $data['ref'] ?? 'refs/heads/' . BRANCH;
$branch = str_replace('refs/heads/', '', $branch);

logMessage("📝 DEPLOYMENT INFO:", 1);
logMessage("Pusher: {$pusherName}", 2);
logMessage("Branch: {$branch}", 2);
logMessage("Commit: {$commitMessage}", 2);
logSeparator('-');

// Change to project directory and execute deployment commands
chdir(PROJECT_PATH);

// Define full paths
$sshKey = '/home2/yyfcolmy/.ssh/github_deploy_3';
$sshCommand = 'ssh -i ' . $sshKey . ' -o IdentitiesOnly=yes -o StrictHostKeyChecking=accept-new';
$gitCommand = 'cd ' . PROJECT_PATH . ' && GIT_SSH_COMMAND=' . escapeshellarg($sshCommand) . ' git pull origin ' . BRANCH . ' 2>&1';
$phpPath = '/usr/local/bin/php'; // Bluehost PHP path, adjust if needed

$commands = [
    $gitCommand,
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

logMessage("⚙️  EXECUTING DEPLOYMENT COMMANDS:", 1);

foreach ($commands as $command) {
    // Extract command name for cleaner display
    $commandName = 'Unknown';
    if (strpos($command, 'git pull') !== false) $commandName = 'Git Pull';
    elseif (strpos($command, 'config:clear') !== false) $commandName = 'Clear Config Cache';
    elseif (strpos($command, 'cache:clear') !== false) $commandName = 'Clear App Cache';
    elseif (strpos($command, 'route:clear') !== false) $commandName = 'Clear Route Cache';
    elseif (strpos($command, 'view:clear') !== false) $commandName = 'Clear View Cache';
    elseif (strpos($command, 'config:cache') !== false) $commandName = 'Cache Config';
    elseif (strpos($command, 'route:cache') !== false) $commandName = 'Cache Routes';
    elseif (strpos($command, 'view:cache') !== false) $commandName = 'Cache Views';
    elseif (strpos($command, 'migrate') !== false) $commandName = 'Run Migrations';
    
    logMessage("", 0); // Empty line for spacing
    logMessage("Step {$stepNumber}: {$commandName}", 2);
    exec($command, $output, $returnCode);
    
    $commandOutput = implode("\n", $output);
    
    if ($returnCode === 0) {
        logMessage("✓ Success", 3);
        if (!empty($commandOutput)) {
            // Show condensed output for successful commands
            $lines = explode("\n", trim($commandOutput));
            $summaryLines = array_slice($lines, -3); // Show last 3 lines
            foreach ($summaryLines as $line) {
                if (!empty(trim($line))) {
                    logMessage(trim($line), 4);
                }
            }
        }
    } else {
        logMessage("❌ Failed (exit code: {$returnCode})", 3);
        $allSuccess = false;
        // Show full output for failed commands
        if (!empty($commandOutput)) {
            foreach (explode("\n", $commandOutput) as $line) {
                if (!empty(trim($line))) {
                    logMessage(trim($line), 4);
                }
            }
        }
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

logMessage("", 0); // Empty line
logSeparator('-');

logMessage("", 0); // Empty line
logSeparator('-');

// Summary
$successCount = count(array_filter($results, fn($r) => $r['success']));
$totalCount = count($results);

if ($allSuccess) {
    logMessage("✅ DEPLOYMENT SUCCESSFUL", 1);
    logMessage("All {$totalCount} steps completed successfully", 2);
} else {
    logMessage("⚠️  DEPLOYMENT COMPLETED WITH ERRORS", 1);
    logMessage("{$successCount}/{$totalCount} steps successful", 2);
    logMessage("", 0);
    logMessage("Failed steps:", 2);
    foreach ($results as $result) {
        if (!$result['success']) {
            logMessage("• Step {$result['step']}: {$result['name']}", 3);
        }
    }
}

logMessage("", 0);
logMessage("Timestamp: " . date('Y-m-d H:i:s'), 2);
logSeparator();
logMessage("", 0); // Extra spacing between deployments

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
