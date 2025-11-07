<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 3px solid #1976d2;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #1976d2;
            margin: 0;
            font-size: 28px;
        }
        .header p {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 14px;
        }
        .content {
            color: #333;
            font-size: 16px;
            line-height: 1.8;
        }
        .content h2 {
            color: #1976d2;
            margin-top: 25px;
            margin-bottom: 15px;
        }
        .content h3 {
            color: #424242;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .content p {
            margin: 15px 0;
        }
        .content ul, .content ol {
            margin: 15px 0;
            padding-left: 25px;
        }
        .content li {
            margin: 8px 0;
        }
        .content a {
            color: #1976d2;
            text-decoration: none;
        }
        .content a:hover {
            text-decoration: underline;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .footer a {
            color: #1976d2;
            text-decoration: none;
            margin: 0 10px;
        }
        .footer a:hover {
            text-decoration: underline;
        }
        .unsubscribe {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .unsubscribe a {
            color: #d32f2f;
            font-weight: 500;
        }
        @media only screen and (max-width: 600px) {
            body {
                padding: 10px;
            }
            .email-container {
                padding: 20px;
            }
            .header h1 {
                font-size: 24px;
            }
            .content {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>📊 Financial Dashboard</h1>
            <p>Market Insights & Company Analysis</p>
        </div>

        <div class="content">
            <h2>{{ $title }}</h2>
            
            {!! nl2br(e($content)) !!}
        </div>

        <div class="footer">
            <p>
                <strong>Financial Dashboard</strong><br>
                Your trusted source for financial insights and company analysis
            </p>
            <p>
                <a href="{{ url('/') }}">Visit Dashboard</a> |
                <a href="{{ url('/blog') }}">Knowledge Hub</a> |
                <a href="{{ url('/contact') }}">Contact Us</a>
            </p>
            <p style="margin-top: 15px;">
                &copy; {{ date('Y') }} Financial Dashboard. All rights reserved.
            </p>
        </div>

        <div class="unsubscribe">
            <p>
                Don't want to receive these emails?<br>
                <a href="{{ $unsubscribeUrl }}">Unsubscribe</a>
            </p>
        </div>
    </div>
</body>
</html>
