<x-mail::message>
# Welcome to Financial Dashboard, {{ $user->name }}!

Thank you for joining our Financial Dashboard platform. We're excited to have you on board!

## What You Can Do

- **Analyze Financial Data**: Access comprehensive financial metrics and ratios for companies
- **Create Watchlists**: Track your favorite companies and get alerts on important changes
- **Compare Companies**: Side-by-side comparison of financial performance
- **Export Reports**: Generate PDF, CSV, and Excel reports for your analysis
- **Stay Updated**: Subscribe to newsletters and weekly digests with market insights

## Get Started

<x-mail::button :url="config('app.url')">
Visit Your Dashboard
</x-mail::button>

## Need Help?

Check out our knowledge hub for guides and tutorials, or contact our support team if you have any questions.

Thanks,<br>
{{ config('app.name') }} Team
</x-mail::message>
