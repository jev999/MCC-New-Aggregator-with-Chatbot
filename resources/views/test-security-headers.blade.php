<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Headers Test</title>
    <style @nonce>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        h1 {
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        .status {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 600;
            margin-bottom: 2rem;
        }
        .status.success {
            background: #10b981;
            color: white;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        .header-table th,
        .header-table td {
            text-align: left;
            padding: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
        }
        .header-table th {
            background: #f3f4f6;
            font-weight: 600;
            color: #374151;
        }
        .header-table tr:hover {
            background: #f9fafb;
        }
        .header-name {
            color: #667eea;
            font-weight: 600;
        }
        .header-value {
            font-family: 'Courier New', monospace;
            color: #059669;
            font-size: 0.875rem;
        }
        .test-section {
            margin-top: 2rem;
            padding: 1rem;
            background: #f9fafb;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        .test-section h3 {
            margin-top: 0;
            color: #374151;
        }
        .inline-script-test {
            padding: 1rem;
            background: #fef3c7;
            border-radius: 6px;
            margin-top: 1rem;
        }
        .nonce-info {
            background: #dbeafe;
            padding: 1rem;
            border-radius: 6px;
            margin-top: 1rem;
        }
        .check {
            color: #10b981;
            margin-right: 0.5rem;
        }
        .info {
            color: #3b82f6;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🛡️ Security Headers Test Page</h1>
        <div class="status success">
            ✓ Security Headers Middleware Active
        </div>

        <p class="info">
            <strong>Info:</strong> This page tests the SecurityHeaders middleware implementation. 
            Check your browser's DevTools (Network tab → Response Headers) to see the actual headers.
        </p>

        <h2>Expected Security Headers</h2>
        <table class="header-table">
            <thead>
                <tr>
                    <th>Header Name</th>
                    <th>Expected Value</th>
                    <th>Purpose</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="header-name">X-Frame-Options</td>
                    <td class="header-value">SAMEORIGIN</td>
                    <td>Prevents clickjacking attacks</td>
                </tr>
                <tr>
                    <td class="header-name">X-Content-Type-Options</td>
                    <td class="header-value">nosniff</td>
                    <td>Prevents MIME-sniffing attacks</td>
                </tr>
                <tr>
                    <td class="header-name">X-XSS-Protection</td>
                    <td class="header-value">1; mode=block</td>
                    <td>Legacy XSS protection</td>
                </tr>
                <tr>
                    <td class="header-name">Referrer-Policy</td>
                    <td class="header-value">strict-origin-when-cross-origin</td>
                    <td>Controls referrer information</td>
                </tr>
                <tr>
                    <td class="header-name">Permissions-Policy</td>
                    <td class="header-value">geolocation=(), microphone=(), camera=()</td>
                    <td>Disables dangerous browser features</td>
                </tr>
                <tr>
                    <td class="header-name">Content-Security-Policy</td>
                    <td class="header-value">default-src 'self'; script-src 'self' 'nonce-...'</td>
                    <td>Advanced XSS protection</td>
                </tr>
                <tr>
                    <td class="header-name">Strict-Transport-Security</td>
                    <td class="header-value">max-age=31536000; includeSubDomains</td>
                    <td>Forces HTTPS (Production only)</td>
                </tr>
            </tbody>
        </table>

        <div class="test-section">
            <h3>CSP Nonce Test</h3>
            <div class="nonce-info">
                <strong>Current Nonce:</strong> <code>{{ csp_nonce() }}</code>
            </div>
            <p>
                <span class="check">✓</span>
                This inline style block uses <code>@nonce</code> directive - it should work with CSP.
            </p>
            <p>
                <span class="check">✓</span>
                The inline script below also uses <code>@nonce</code> - it should execute successfully.
            </p>
            <div class="inline-script-test" id="scriptTest">
                Waiting for inline script test...
            </div>
        </div>

        <div class="test-section">
            <h3>How to Verify Headers in Browser</h3>
            <ol>
                <li>Open Browser DevTools (Press F12)</li>
                <li>Go to the <strong>Network</strong> tab</li>
                <li>Refresh this page</li>
                <li>Click on this page's request (test-security-headers)</li>
                <li>Go to the <strong>Response Headers</strong> section</li>
                <li>Verify all security headers are present</li>
            </ol>
        </div>

        <div class="test-section">
            <h3>User Dashboard Compatibility</h3>
            <p>
                The security headers are designed to work seamlessly with your existing user dashboard.
                All inline scripts and styles that use the <code>@nonce</code> directive will continue to work.
            </p>
            <p>
                <a href="/user/dashboard" style="color: #667eea; font-weight: 600;">
                    → Test User Dashboard
                </a>
            </p>
        </div>
    </div>

    <script @nonce>
        // This inline script uses @nonce directive and should work with CSP
        document.addEventListener('DOMContentLoaded', function() {
            const testDiv = document.getElementById('scriptTest');
            testDiv.innerHTML = '<strong style="color: #10b981;">✓ Inline script executed successfully!</strong> CSP nonce is working.';
            testDiv.style.background = '#d1fae5';
            
            // Log nonce to console
            console.log('%c✓ Security Headers Test', 'color: #10b981; font-weight: bold; font-size: 16px;');
            console.log('CSP Nonce:', '{{ csp_nonce() }}');
            console.log('Inline scripts with @nonce directive work correctly!');
        });
    </script>
</body>
</html>
