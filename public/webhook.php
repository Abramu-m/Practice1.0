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

function sendJsonResponse(array $payload, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($payload);
}

function ackAndContinue(array $payload, int $statusCode = 202): void {
    sendJsonResponse($payload, $statusCode);

    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
        return;
    }

    if (function_exists('apache_setenv')) {
        @apache_setenv('no-gzip', '1');
    }

    @ini_set('zlib.output_compression', '0');

    while (ob_get_level() > 0) {
        @ob_end_flush();
    }

    flush();
}

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
    sendJsonResponse(['error' => 'Invalid signature'], 403);
    exit;
}

Log::channel('webhook')->info('✓ Signature verified successfully');

// Decode payload
$decodedPayload = $payload;
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

if (stripos($contentType, 'application/x-www-form-urlencoded') !== false) {
    parse_str($payload, $formData);
    if (!empty($formData['payload'])) {
        $decodedPayload = $formData['payload'];
    }
}

$data = json_decode($decodedPayload, true);
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

ignore_user_abort(true);
set_time_limit(0);

ackAndContinue([
    'status' => 'accepted',
    'message' => 'Deployment started',
    'pusher' => $pusherName,
    'branch' => $branch,
    'commit' => $commitMessage,
    'timestamp' => date('Y-m-d H:i:s')
], 202);

// Change to project directory and execute deployment commands
chdir(PROJECT_PATH);

// Define full paths
$sshKey = '/home2/yyfcolmy/.ssh/github_deploy_3';
$sshCommand = 'ssh -i ' . $sshKey . ' -o IdentitiesOnly=yes -o StrictHostKeyChecking=accept-new';
$repositoryFullName = $data['repository']['full_name'] ?? 'Abramu-m/Practice1.0';
$repoSshUrl = 'git@github.com:' . $repositoryFullName . '.git';
$gitCommand =
    'cd ' . PROJECT_PATH .
    ' && GIT_SSH_COMMAND=' . escapeshellarg($sshCommand) .
    ' git fetch ' . escapeshellarg($repoSshUrl) . ' ' . escapeshellarg(BRANCH) . ' 2>&1' .
    ' && cd ' . PROJECT_PATH .
    ' && git reset --hard FETCH_HEAD 2>&1';
$phpPath = '/usr/local/bin/php'; // Bluehost PHP path, adjust if needed

$commands = [
    'cd ' . PROJECT_PATH . ' && HOME=' . PROJECT_PATH . '/storage/composer-home COMPOSER_HOME=' . PROJECT_PATH . '/storage/composer-home /opt/cpanel/composer/bin/composer install --no-dev --optimize-autoloader --ignore-platform-reqs 2>&1',
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

// Run git pull first with one-time self-heal for corrupted git index
$gitOutput = [];
$gitReturnCode = 0;
exec($gitCommand, $gitOutput, $gitReturnCode);
$gitOutputText = implode("\n", $gitOutput);

$isGitCorruption = function ($outputText) {
    return
        (stripos($outputText, 'unknown index entry format') !== false) ||
        (stripos($outputText, 'index file corrupt') !== false) ||
        (stripos($outputText, 'fatal: index') !== false) ||
        (stripos($outputText, 'loose object') !== false) ||
        (stripos($outputText, 'unable to unpack') !== false) ||
        (stripos($outputText, 'did not send all necessary objects') !== false) ||
        (stripos($outputText, 'inflate: data stream error') !== false);
};

if ($gitReturnCode !== 0) {
    $indexCorruptionDetected = $isGitCorruption($gitOutputText);

    if ($indexCorruptionDetected) {
        Log::channel('webhook')->warning('Git repository corruption detected, attempting auto-repair');

        $repairCommands = [
            'cd ' . PROJECT_PATH . ' && rm -f .git/index 2>&1',
            'cd ' . PROJECT_PATH . ' && git reset --hard HEAD 2>&1',
            'cd ' . PROJECT_PATH . ' && GIT_SSH_COMMAND=' . escapeshellarg($sshCommand) . ' git fetch ' . escapeshellarg($repoSshUrl) . ' ' . escapeshellarg(BRANCH) . ' 2>&1',
        ];

        foreach ($repairCommands as $repairCommand) {
            $repairOutput = [];
            $repairCode = 0;
            exec($repairCommand, $repairOutput, $repairCode);
        }

        $gitOutput = [];
        $gitReturnCode = 0;
        exec($gitCommand, $gitOutput, $gitReturnCode);
        $gitOutputText = implode("\n", $gitOutput);

        if ($gitReturnCode !== 0 && $isGitCorruption($gitOutputText)) {
            Log::channel('webhook')->warning('Git corruption persisted, rebuilding .git metadata from remote');

            $rebuildCommands = [
                'cd ' . PROJECT_PATH . ' && rm -rf .git 2>&1',
                'cd ' . PROJECT_PATH . ' && git init 2>&1',
                'cd ' . PROJECT_PATH . ' && git remote add origin ' . escapeshellarg($repoSshUrl) . ' 2>&1',
                'cd ' . PROJECT_PATH . ' && GIT_SSH_COMMAND=' . escapeshellarg($sshCommand) . ' git fetch --depth=1 origin ' . escapeshellarg(BRANCH) . ' 2>&1',
                'cd ' . PROJECT_PATH . ' && git checkout -B ' . escapeshellarg(BRANCH) . ' FETCH_HEAD 2>&1',
            ];

            $rebuildOutputLines = [];
            $rebuildReturnCode = 0;

            foreach ($rebuildCommands as $rebuildCommand) {
                $singleOutput = [];
                $singleCode = 0;
                exec($rebuildCommand, $singleOutput, $singleCode);
                $rebuildOutputLines = array_merge($rebuildOutputLines, $singleOutput);

                if ($singleCode !== 0) {
                    $rebuildReturnCode = $singleCode;
                    break;
                }
            }

            if ($rebuildReturnCode === 0) {
                $gitReturnCode = 0;
                $gitOutputText = "Rebuilt .git metadata and checked out latest branch successfully.";
            } else {
                $gitReturnCode = $rebuildReturnCode;
                $gitOutputText = implode("\n", $rebuildOutputLines);
            }
        }
    }
}

$gitSuccess = $gitReturnCode === 0;

if ($gitSuccess) {
    Log::channel('webhook')->info('✓ Step 1: Git Pull - Success', [
        'exit_code' => $gitReturnCode,
        'output' => !empty($gitOutputText) ? array_slice(explode("\n", trim($gitOutputText)), -3) : []
    ]);
} else {
    Log::channel('webhook')->error('❌ Step 1: Git Pull - Failed', [
        'exit_code' => $gitReturnCode,
        'output' => !empty($gitOutputText) ? explode("\n", trim($gitOutputText)) : []
    ]);
    $allSuccess = false;
}

$results[] = [
    'step' => $stepNumber,
    'name' => 'Git Pull',
    'command' => $gitCommand,
    'output' => $gitOutputText,
    'success' => $gitSuccess
];

$stepNumber++;

if (!$gitSuccess) {
    Log::channel('webhook')->warning('Stopping deployment because git pull failed');

    Log::channel('webhook')->info('----------------------------------------------------------');

    $successCount = count(array_filter($results, fn($r) => $r['success']));
    $totalCount = count($results);

    Log::channel('webhook')->warning('⚠️  DEPLOYMENT COMPLETED WITH ERRORS', [
        'total_steps' => $totalCount,
        'successful_steps' => $successCount,
        'failed_steps' => $totalCount - $successCount,
        'failed_step_names' => ['Step 1: Git Pull'],
        'timestamp' => date('Y-m-d H:i:s')
    ]);

    Log::channel('webhook')->info('==========================================================');
    Log::channel('webhook')->info('');
    exit;
}

foreach ($commands as $command) {
    // Extract command name for cleaner display
    $commandName = 'Unknown';
    if (strpos($command, 'composer install') !== false) $commandName = 'Install Dependencies';
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
