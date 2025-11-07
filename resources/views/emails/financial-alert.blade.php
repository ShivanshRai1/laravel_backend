<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Alert</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .alert-box {
            background: white;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .change-item {
            background: #fff;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            border: 1px solid #e0e0e0;
        }
        .positive {
            color: #4caf50;
            font-weight: bold;
        }
        .negative {
            color: #f44336;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 12px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Financial Dashboard Alert</h1>
        <p>Alert for {{ $company->name }} ({{ $company->symbol }})</p>
    </div>
    
    <div class="content">
        <h2>Hello {{ $user->name }},</h2>
        
        <div class="alert-box">
            <p><strong>{{ $alertContent['message'] }}</strong></p>
        </div>

        @if($alertType === 'ratio_change' && isset($alertContent['changes']))
            <h3>Changes Detected:</h3>
            @foreach($alertContent['changes'] as $change)
                <div class="change-item">
                    <strong>{{ $change['metric'] }}</strong> ({{ $change['quarter'] }})<br>
                    <span class="{{ $change['change_percent'] >= 0 ? 'positive' : 'negative' }}">
                        {{ $change['change_percent'] > 0 ? '+' : '' }}{{ $change['change_percent'] }}%
                    </span><br>
                    <small>
                        Latest: ${{ number_format($change['latest_value'], 2) }}M | 
                        Previous: ${{ number_format($change['previous_value'], 2) }}M
                    </small>
                </div>
            @endforeach
        @endif

        <p style="margin-top: 30px;">
            <a href="{{ config('app.frontend_url') }}/companies/{{ $company->symbol }}" class="button">
                View Company Details
            </a>
        </p>

        <p style="margin-top: 20px; font-size: 14px; color: #666;">
            You're receiving this email because you have email alerts enabled for this company. 
            You can manage your alert preferences in your dashboard settings.
        </p>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} Financial Dashboard. All rights reserved.</p>
        <p>
            <a href="{{ config('app.frontend_url') }}/settings" style="color: #667eea;">Manage Preferences</a> | 
            <a href="{{ config('app.frontend_url') }}/unsubscribe" style="color: #667eea;">Unsubscribe</a>
        </p>
    </div>
</body>
</html>
