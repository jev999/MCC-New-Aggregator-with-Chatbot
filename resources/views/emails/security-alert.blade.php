<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Alert - MCC News Aggregator</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #dc3545;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 20px;
            border: 1px solid #dee2e6;
        }
        .alert-box {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .data-table th,
        .data-table td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
        }
        .data-table th {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .footer {
            background-color: #6c757d;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 0 0 5px 5px;
            font-size: 12px;
        }
        .timestamp {
            color: #6c757d;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🚨 Security Alert</h1>
        <p>MCC News Aggregator System</p>
    </div>
    
    <div class="content">
        <div class="alert-box">
            <strong>⚠️ Security Event Detected</strong><br>
            {{ $subject }}
        </div>
        
        <h3>Event Details:</h3>
        <table class="data-table">
            @foreach($data as $key => $value)
            <tr>
                <th>{{ ucfirst(str_replace('_', ' ', $key)) }}</th>
                <td>
                    @if(is_array($value))
                        <pre>{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                    @else
                        {{ $value }}
                    @endif
                </td>
            </tr>
            @endforeach
        </table>
        
        <div class="timestamp">
            <strong>Alert Generated:</strong> {{ $timestamp }}
        </div>
        
        <p><strong>Action Required:</strong> Please review this security event and take appropriate action if necessary.</p>
        
        <p><strong>Recommendations:</strong></p>
        <ul>
            <li>Review server logs for additional context</li>
            <li>Check for any unauthorized access attempts</li>
            <li>Consider implementing additional security measures if needed</li>
            <li>Monitor for similar patterns in the future</li>
        </ul>
    </div>
    
    <div class="footer">
        <p>This is an automated security alert from the MCC News Aggregator system.</p>
        <p>Please do not reply to this email.</p>
    </div>
</body>
</html>
