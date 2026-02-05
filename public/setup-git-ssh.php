<?php
/**
 * One-time setup script to configure Git SSH for webhook
 * Access: https://janet-healthcare.com/setup-git-ssh.php
 * DELETE THIS FILE AFTER RUNNING!
 */

// Security: Simple password protection
define('SETUP_PASSWORD', 'setup2026'); // Change this!

if (!isset($_GET['password']) || $_GET['password'] !== SETUP_PASSWORD) {
    die('Access denied. Use: ?password=setup2026');
}

echo "<pre>";
echo "=== Git SSH Setup Script ===\n\n";

$projectPath = '/home2/yyfcolmy/practice1.0/Practice1.0';
$sshKey = '/home2/yyfcolmy/.ssh/github_deploy_3';

// Change to project directory
chdir($projectPath);
echo "Changed to: " . getcwd() . "\n\n";

// 1. Check current remote
echo "1. Current git remote:\n";
exec('git remote -v 2>&1', $output1);
echo implode("\n", $output1) . "\n\n";

// 2. Change remote to SSH
echo "2. Changing remote to SSH...\n";
exec('git remote set-url origin git@github.com:Abramu-m/Practice1.0.git 2>&1', $output2, $return2);
if ($return2 === 0) {
    echo "✓ Remote changed successfully\n\n";
} else {
    echo "✗ Failed: " . implode("\n", $output2) . "\n\n";
}

// 3. Add GitHub to known_hosts
echo "3. Adding GitHub to known_hosts...\n";
exec('ssh-keyscan github.com >> ~/.ssh/known_hosts 2>&1', $output3, $return3);
if ($return3 === 0) {
    echo "✓ GitHub added to known_hosts\n\n";
} else {
    echo "✗ Warning: " . implode("\n", $output3) . "\n\n";
}

// 4. Verify new remote
echo "4. New git remote:\n";
exec('git remote -v 2>&1', $output4);
echo implode("\n", $output4) . "\n\n";

// 5. Check deploy key file
echo "5. Checking deploy key file...\n";
exec('ls -l ' . $sshKey . ' ' . $sshKey . '.pub 2>&1', $output5a, $return5a);
echo implode("\n", $output5a) . "\n\n";

// 6. Test SSH connection to GitHub (using deploy key)
echo "6. Testing SSH connection to GitHub...\n";
exec('ssh -i ' . $sshKey . ' -o IdentitiesOnly=yes -o StrictHostKeyChecking=accept-new -T git@github.com 2>&1', $output5, $return5);
echo implode("\n", $output5) . "\n\n";

// 7. Test git pull
echo "7. Testing git pull...\n";
$gitSsh = 'GIT_SSH_COMMAND=' . escapeshellarg('ssh -i ' . $sshKey . ' -o IdentitiesOnly=yes -o StrictHostKeyChecking=accept-new');
exec($gitSsh . ' git pull origin master 2>&1', $output6, $return6);
if ($return6 === 0) {
    echo "✓ SUCCESS! Git pull works!\n";
    echo implode("\n", $output6) . "\n\n";
} else {
    echo "✗ Git pull failed:\n";
    echo implode("\n", $output6) . "\n\n";
}

echo "\n=== Setup Complete ===\n";
echo "If everything worked, your webhook will now work perfectly!\n";
echo "\n⚠️  IMPORTANT: DELETE THIS FILE NOW FOR SECURITY!\n";
echo "Run: rm /home2/yyfcolmy/practice1.0/Practice1.0/public/setup-git-ssh.php\n";
echo "</pre>";
