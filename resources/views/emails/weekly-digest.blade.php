<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Digest</title>
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
            padding: 30px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .stat-box {
            background: white;
            padding: 20px;
            margin: 15px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
        }
        .company-list {
            list-style: none;
            padding: 0;
        }
        .company-item {
            background: white;
            padding: 15px;
            margin: 10px 0;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .positive {
            color: #4caf50;
            font-weight: bold;
        }
        .negative {
            color: #f44336;
            font-weight: bold;
        }
        .blog-item {
            background: white;
            padding: 15px;
            margin: 10px 0;
            border-radius: 6px;
            border-left: 4px solid #667eea;
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
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 12px;
        }
        h2 {
            color: #667eea;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Your Weekly Financial Digest</h1>
        <p>{{ $digestData['week_start'] }} - {{ $digestData['week_end'] }}</p>
    </div>
    
    <div class="content">
        <h2>Hello {{ $user->name }},</h2>
        
        <p>Here's your weekly summary of financial activities and updates from your dashboard.</p>

        <!-- Statistics -->
        <div class="stat-box">
            <div class="stat-number">{{ $digestData['watchlist_count'] }}</div>
            <div>Companies in your watchlist</div>
        </div>

        <div class="stat-box">
            <div class="stat-number">{{ $digestData['financial_updates_count'] }}</div>
            <div>New financial data uploads this week</div>
        </div>

        <!-- Top Gainers -->
        @if(count($digestData['top_gainers']) > 0)
            <h2>🚀 Top Gainers</h2>
            <ul class="company-list">
                @foreach($digestData['top_gainers'] as $gainer)
                    <li class="company-item">
                        <span>
                            <strong>{{ $gainer['company'] }}</strong><br>
                            <small>{{ $gainer['symbol'] }}</small>
                        </span>
                        <span class="positive">+{{ $gainer['growth'] }}%</span>
                    </li>
                @endforeach
            </ul>
        @endif

        <!-- Top Losers -->
        @if(count($digestData['top_losers']) > 0 && $digestData['top_losers'][0]['growth'] < 0)
            <h2>📉 Top Decliners</h2>
            <ul class="company-list">
                @foreach($digestData['top_losers'] as $loser)
                    @if($loser['growth'] < 0)
                        <li class="company-item">
                            <span>
                                <strong>{{ $loser['company'] }}</strong><br>
                                <small>{{ $loser['symbol'] }}</small>
                            </span>
                            <span class="negative">{{ $loser['growth'] }}%</span>
                        </li>
                    @endif
                @endforeach
            </ul>
        @endif

        <!-- Recent Blog Posts -->
        @if($digestData['recent_blogs']->count() > 0)
            <h2>📰 Latest Articles</h2>
            @foreach($digestData['recent_blogs'] as $blog)
                <div class="blog-item">
                    <strong>{{ $blog->title }}</strong><br>
                    <small>{{ $blog->created_at->format('M d, Y') }}</small>
                    @if($blog->meta_description)
                        <p style="margin: 10px 0; color: #666;">{{ Str::limit($blog->meta_description, 100) }}</p>
                    @endif
                </div>
            @endforeach
        @endif

        <p style="text-align: center; margin-top: 30px;">
            <a href="{{ config('app.frontend_url') }}/user/dashboard" class="button">
                View Your Dashboard
            </a>
        </p>

        <p style="margin-top: 20px; font-size: 14px; color: #666;">
            You're receiving this weekly digest because you subscribed to email updates. 
            You can change your preferences or unsubscribe at any time.
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
