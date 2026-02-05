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
function logMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] {$message}\n";
    file_put_contents(LOG_FILE, $logEntry, FILE_APPEND);
    echo $logEntry;
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
logMessage("=== Webhook triggered ===");

// Get payload
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

// Verify signature
if (!verifyGitHubSignature($payload, $signature)) {
    logMessage("ERROR: Invalid signature");
    http_response_code(403);
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

logMessage("Signature verified successfully");

// Decode payload
$data = json_decode($payload, true);
$pusherName = $data['pusher']['name'] ?? 'Unknown';
$commitMessage = $data['head_commit']['message'] ?? 'No message';

logMessage("Push by: {$pusherName}");
logMessage("Commit message: {$commitMessage}");

// Change to project directory and execute deployment commands
chdir(PROJECT_PATH);

// Define full paths
$sshKey = '/home2/yyfcolmy/.ssh/github_deploy';
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

foreach ($commands as $command) {
    logMessage("Executing: {$command}");
    exec($command, $output, $returnCode);
    
    $commandOutput = implode("\n", $output);
    logMessage("Output: {$commandOutput}");
    
    $results[] = [
        'command' => $command,
        'output' => $commandOutput,
        'success' => $returnCode === 0
    ];
    
    if ($returnCode !== 0) {
        $allSuccess = false;
        logMessage("ERROR: Command failed with code {$returnCode}");
    }
    
    $output = []; // Clear for next command
}

logMessage("=== Deployment " . ($allSuccess ? "completed successfully" : "completed with errors") . " ===\n");

// Return response
http_response_code($allSuccess ? 200 : 500);
echo json_encode([
    'status' => $allSuccess ? 'success' : 'partial_failure',
    'pusher' => $pusherName,
    'commit' => $commitMessage,
    'results' => $results,
    'timestamp' => date('Y-m-d H:i:s')
]);
