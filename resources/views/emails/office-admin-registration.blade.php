<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Office Admin Registration - MCC News Aggregator</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .header p {
            margin: 10px 0 0 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .content {
            padding: 40px 30px;
        }
        .welcome-text {
            font-size: 18px;
            margin-bottom: 20px;
            color: #2d3748;
        }
        .info-box {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .info-box h3 {
            margin: 0 0 10px 0;
            color: #1976d2;
            font-size: 16px;
        }
        .info-box p {
            margin: 0;
            color: #1976d2;
        }
        .register-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
        }
        .register-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }
        .instructions {
            background-color: #fff3e0;
            border-left: 4px solid #ff9800;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .instructions h3 {
            margin: 0 0 15px 0;
            color: #f57c00;
            font-size: 16px;
        }
        .instructions ul {
            margin: 0;
            padding-left: 20px;
            color: #f57c00;
        }
        .instructions li {
            margin-bottom: 8px;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        .footer p {
            margin: 0;
            color: #718096;
            font-size: 14px;
        }
        .security-note {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .security-note p {
            margin: 0;
            color: #dc2626;
            font-size: 14px;
        }
        @media (max-width: 600px) {
            .email-container {
                margin: 0;
                border-radius: 0;
            }
            .content {
                padding: 30px 20px;
            }
            .header {
                padding: 25px 20px;
            }
            .header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>🏢 MCC News Aggregator</h1>
            <p>Office Admin Registration</p>
        </div>
        
        <div class="content">
            <div class="welcome-text">
                <strong>Hello!</strong>
            </div>
            
            <p>You have been invited to become an Office Admin for the <strong>{{ $office }}</strong> office in the MCC News Aggregator system.</p>
            
            <div class="info-box">
                <h3>📧 Registration Details</h3>
                <p><strong>Email:</strong> {{ $email }}</p>
                <p><strong>Office:</strong> {{ $office }}</p>
                <p><strong>Role:</strong> Office Admin</p>
            </div>
            
            <p>As an Office Admin, you will be able to:</p>
            <ul>
                <li>Create and manage announcements for your office</li>
                <li>Post and manage events for your office</li>
                <li>Create and manage news articles for your office</li>
                <li>View and manage content specific to your office</li>
            </ul>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $registrationUrl }}" class="register-button">
                    🚀 Complete Registration
                </a>
            </div>
            
            <div class="instructions">
                <h3>📋 Registration Instructions</h3>
                <ul>
                    <li>Click the "Complete Registration" button above</li>
                    <li>You will be redirected to a secure registration form</li>
                    <li>Create a strong password for your account</li>
                    <li>Complete the registration process</li>
                    <li>You can then login to the admin dashboard</li>
                </ul>
            </div>
            
            <div class="security-note">
                <p><strong>⚠️ Security Notice:</strong> This registration link will expire in 30 minutes for security purposes. If the link expires, please contact the Super Admin for a new registration link.</p>
            </div>
            
            <p>If you have any questions or need assistance, please contact the system administrator.</p>
            
            <p>Best regards,<br>
            <strong>MCC News Aggregator Team</strong></p>
        </div>
        
        <div class="footer">
            <p>This is an automated email from MCC News Aggregator System.</p>
            <p>Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
