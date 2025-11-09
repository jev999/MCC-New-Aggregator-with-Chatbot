<?php
/**
 * EMERGENCY DEPLOYMENT SCRIPT
 * 
 * Access via: https://mcc-nac.com/deploy.php?secret=YOUR_SECRET_KEY
 * 
 * SECURITY WARNING: Delete this file after deployment!
 */

// Set your secret key here (change this to something secure!)
define('DEPLOY_SECRET', 'mcc_deploy_2025_secure_key');

// Check authentication
if (!isset($_GET['secret']) || $_GET['secret'] !== DEPLOY_SECRET) {
    http_response_code(403);
    die('Access Denied');
}

// Function to execute command
function executeCommand($command) {
    $output = [];
    $return_var = 0;
    exec($command . ' 2>&1', $output, $return_var);
    return [
        'command' => $command,
        'output' => implode("\n", $output),
        'success' => $return_var === 0
    ];
}

// Start deployment
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>MCC-NAC Deployment</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem;
            margin: 0;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h1 {
            color: #1e293b;
            border-bottom: 3px solid #667eea;
            padding-bottom: 1rem;
        }
        .step {
            background: #f8fafc;
            border-left: 4px solid #10b981;
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 8px;
        }
        .step.error {
            border-left-color: #ef4444;
            background: #fef2f2;
        }
        .step.success {
            border-left-color: #10b981;
            background: #f0fdf4;
        }
        .output {
            background: #1e293b;
            color: #10b981;
            padding: 1rem;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
            white-space: pre-wrap;
            margin-top: 0.5rem;
            max-height: 300px;
            overflow-y: auto;
        }
        .warning {
            background: #fef3c7;
            border: 2px solid #f59e0b;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
        }
        .success-box {
            background: #d1fae5;
            border: 2px solid #10b981;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 MCC-NAC Emergency Deployment</h1>
        
        <?php
        echo "<div class='step'>";
        echo "<strong>📍 Current Directory:</strong> " . getcwd();
        echo "</div>";

        // Step 1: Git Pull
        echo "<div class='step'>";
        echo "<h3>Step 1: Pulling latest changes from GitHub</h3>";
        $result = executeCommand('git pull origin main');
        echo "<div class='output'>" . htmlspecialchars($result['output']) . "</div>";
        if ($result['success']) {
            echo "<p style='color: #10b981; font-weight: bold;'>✅ Git pull successful!</p>";
        } else {
            echo "<p style='color: #ef4444; font-weight: bold;'>❌ Git pull failed!</p>";
        }
        echo "</div>";

        // Step 2: Clear View Cache
        echo "<div class='step'>";
        echo "<h3>Step 2: Clearing view cache</h3>";
        $result = executeCommand('php artisan view:clear');
        echo "<div class='output'>" . htmlspecialchars($result['output']) . "</div>";
        if ($result['success']) {
            echo "<p style='color: #10b981; font-weight: bold;'>✅ View cache cleared!</p>";
        }
        echo "</div>";

        // Step 3: Clear Config Cache
        echo "<div class='step'>";
        echo "<h3>Step 3: Clearing config cache</h3>";
        $result = executeCommand('php artisan config:clear');
        echo "<div class='output'>" . htmlspecialchars($result['output']) . "</div>";
        if ($result['success']) {
            echo "<p style='color: #10b981; font-weight: bold;'>✅ Config cache cleared!</p>";
        }
        echo "</div>";

        // Step 4: Clear Route Cache
        echo "<div class='step'>";
        echo "<h3>Step 4: Clearing route cache</h3>";
        $result = executeCommand('php artisan route:clear');
        echo "<div class='output'>" . htmlspecialchars($result['output']) . "</div>";
        if ($result['success']) {
            echo "<p style='color: #10b981; font-weight: bold;'>✅ Route cache cleared!</p>";
        }
        echo "</div>";

        // Step 5: Clear Application Cache
        echo "<div class='step'>";
        echo "<h3>Step 5: Clearing application cache</h3>";
        $result = executeCommand('php artisan cache:clear');
        echo "<div class='output'>" . htmlspecialchars($result['output']) . "</div>";
        if ($result['success']) {
            echo "<p style='color: #10b981; font-weight: bold;'>✅ Application cache cleared!</p>";
        }
        echo "</div>";

        // Step 6: Optimize
        echo "<div class='step'>";
        echo "<h3>Step 6: Optimizing application</h3>";
        $result = executeCommand('php artisan optimize');
        echo "<div class='output'>" . htmlspecialchars($result['output']) . "</div>";
        if ($result['success']) {
            echo "<p style='color: #10b981; font-weight: bold;'>✅ Application optimized!</p>";
        }
        echo "</div>";

        echo "<div class='success-box'>";
        echo "<h2>✅ Deployment Complete!</h2>";
        echo "<p>Your backup page fixes have been deployed to production.</p>";
        echo "<p><strong>Test it now:</strong> <a href='https://mcc-nac.com/superadmin/backup' target='_blank'>https://mcc-nac.com/superadmin/backup</a></p>";
        echo "</div>";

        echo "<div class='warning'>";
        echo "<h3>⚠️ SECURITY WARNING</h3>";
        echo "<p><strong>DELETE THIS FILE IMMEDIATELY!</strong></p>";
        echo "<p>This deployment script is a security risk if left on the server.</p>";
        echo "<p>Run: <code>rm public/deploy.php</code></p>";
        echo "</div>";
        ?>
    </div>
</body>
</html>
