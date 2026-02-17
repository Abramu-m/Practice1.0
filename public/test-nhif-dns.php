<?php
/**
 * NHIF DNS and Connection Test
 * 
 * IMPORTANT: Delete this file after testing for security reasons!
 * 
 * Usage: Visit yourdomain.com/test-nhif-dns.php in your browser
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>NHIF Connection Test</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            padding: 30px; 
            background: #f5f5f5; 
        }
        .container { 
            background: white; 
            padding: 30px; 
            border-radius: 8px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); 
            max-width: 800px; 
            margin: 0 auto; 
        }
        .test-section { 
            margin: 20px 0; 
            padding: 15px; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
        }
        .success { 
            color: #28a745; 
            background: #d4edda; 
            padding: 10px; 
            border-radius: 4px; 
            margin: 10px 0; 
        }
        .error { 
            color: #721c24; 
            background: #f8d7da; 
            padding: 10px; 
            border-radius: 4px; 
            margin: 10px 0; 
        }
        .warning { 
            color: #856404; 
            background: #fff3cd; 
            padding: 10px; 
            border-radius: 4px; 
            margin: 10px 0; 
        }
        .info { 
            color: #004085; 
            background: #cce5ff; 
            padding: 10px; 
            border-radius: 4px; 
            margin: 10px 0; 
        }
        h1 { color: #333; }
        h2 { color: #555; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        code { 
            background: #f4f4f4; 
            padding: 2px 6px; 
            border-radius: 3px; 
            font-family: 'Courier New', monospace; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 10px 0; 
        }
        table td { 
            padding: 8px; 
            border: 1px solid #ddd; 
        }
        table td:first-child { 
            font-weight: bold; 
            width: 30%; 
            background: #f8f9fa; 
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔍 NHIF Connection Diagnostics</h1>
        
        <?php
        $domain = 'verification.nhif.or.tz';
        $testUrl = 'https://verification.nhif.or.tz/nhifservice/Token';
        $hasErrors = false;
        
        echo "<div class='info'>Testing connection to: <code>{$domain}</code></div>";
        ?>
        
        <!-- DNS Resolution Test -->
        <div class='test-section'>
            <h2>1. DNS Resolution Test</h2>
            <?php
            $ip = gethostbyname($domain);
            if ($ip === $domain) {
                echo "<div class='error'>❌ <strong>DNS Resolution Failed</strong><br>";
                echo "Cannot resolve domain: <code>{$domain}</code><br>";
                echo "This means the server cannot find the IP address for this domain.</div>";
                $hasErrors = true;
            } else {
                echo "<div class='success'>✓ <strong>DNS Resolution Successful</strong><br>";
                echo "Domain: <code>{$domain}</code><br>";
                echo "Resolved IP: <code>{$ip}</code></div>";
            }
            ?>
        </div>
        
        <!-- CURL Connection Test -->
        <div class='test-section'>
            <h2>2. HTTPS Connection Test</h2>
            <?php
            if (function_exists('curl_init')) {
                $ch = curl_init($testUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 15);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_VERBOSE, false);
                
                $result = curl_exec($ch);
                $error = curl_error($ch);
                $errno = curl_errno($ch);
                $info = curl_getinfo($ch);
                curl_close($ch);
                
                if ($error) {
                    echo "<div class='error'>❌ <strong>Connection Failed</strong><br>";
                    echo "Error Code: <code>{$errno}</code><br>";
                    echo "Error Message: <code>{$error}</code><br>";
                    echo "<br><strong>Common Solutions:</strong>";
                    echo "<ul>";
                    echo "<li>Error 6 (DNS): Contact Bluehost support to check DNS settings</li>";
                    echo "<li>Error 28 (Timeout): Check if firewall is blocking outbound HTTPS</li>";
                    echo "<li>Error 60 (SSL): SSL certificate verification issue</li>";
                    echo "</ul></div>";
                    $hasErrors = true;
                } else {
                    echo "<div class='success'>✓ <strong>Connection Successful</strong><br>";
                    echo "HTTP Code: <code>{$info['http_code']}</code></div>";
                }
                
                echo "<table>";
                echo "<tr><td>URL</td><td><code>{$testUrl}</code></td></tr>";
                echo "<tr><td>HTTP Code</td><td>{$info['http_code']}</td></tr>";
                echo "<tr><td>Total Time</td><td>" . round($info['total_time'], 3) . " seconds</td></tr>";
                echo "<tr><td>Connect Time</td><td>" . round($info['connect_time'], 3) . " seconds</td></tr>";
                echo "<tr><td>Primary IP</td><td>{$info['primary_ip']}</td></tr>";
                echo "</table>";
                
            } else {
                echo "<div class='error'>❌ CURL extension is not installed</div>";
                $hasErrors = true;
            }
            ?>
        </div>
        
        <!-- SSL Certificate Test -->
        <div class='test-section'>
            <h2>3. SSL Certificate Test (Without Verification)</h2>
            <?php
            $ch = curl_init($testUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            
            $result = curl_exec($ch);
            $error = curl_error($ch);
            $info = curl_getinfo($ch);
            curl_close($ch);
            
            if ($error) {
                echo "<div class='error'>❌ Even without SSL verification: <code>{$error}</code></div>";
            } else {
                echo "<div class='success'>✓ Connection works without SSL verification (HTTP Code: {$info['http_code']})</div>";
                if ($info['http_code'] == 401 || $info['http_code'] == 400) {
                    echo "<div class='info'>This is expected - the endpoint requires authentication.</div>";
                }
            }
            ?>
        </div>
        
        <!-- Server Information -->
        <div class='test-section'>
            <h2>4. Server Information</h2>
            <table>
                <tr><td>Server IP</td><td><?php echo $_SERVER['SERVER_ADDR'] ?? 'Unknown'; ?></td></tr>
                <tr><td>Server Name</td><td><?php echo $_SERVER['SERVER_NAME'] ?? 'Unknown'; ?></td></tr>
                <tr><td>PHP Version</td><td><?php echo phpversion(); ?></td></tr>
                <tr><td>CURL Version</td><td><?php echo curl_version()['version'] ?? 'N/A'; ?></td></tr>
                <tr><td>OpenSSL Version</td><td><?php echo OPENSSL_VERSION_TEXT ?? 'N/A'; ?></td></tr>
            </table>
        </div>
        
        <!-- Recommendations -->
        <div class='test-section'>
            <h2>5. Recommendations</h2>
            <?php if ($hasErrors): ?>
                <div class='warning'>
                    <strong>⚠ Action Required:</strong>
                    <ol>
                        <li><strong>Contact Bluehost Support</strong> and provide them this information:
                            <ul>
                                <li>Domain: <code><?php echo $domain; ?></code></li>
                                <li>Issue: "Cannot resolve DNS or connect to this domain from server"</li>
                                <li>Request: "Please check if this domain is blocked or if DNS resolution is working properly"</li>
                            </ul>
                        </li>
                        <li><strong>Check Firewall Settings:</strong> Outbound HTTPS connections might be blocked</li>
                        <li><strong>Verify Domain:</strong> Ensure <code><?php echo $domain; ?></code> is accessible from internet</li>
                    </ol>
                </div>
            <?php else: ?>
                <div class='success'>
                    ✓ All tests passed! Your server can connect to NHIF.
                    <br>If your application still has issues, check your Laravel configuration and credentials.
                </div>
            <?php endif; ?>
        </div>
        
        <div class='warning'>
            <strong>🔒 SECURITY NOTICE:</strong> Please delete this file (<code>test-nhif-dns.php</code>) from your server after testing!
        </div>
    </div>
</body>
</html>
