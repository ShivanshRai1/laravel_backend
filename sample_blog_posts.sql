-- Sample Blog Posts for Testing Approval Workflow

-- Post 1: Pending - Ready for approval
INSERT INTO blog_posts (title, slug, excerpt, content, featured_image, user_id, status, tags, views, created_at, updated_at)
VALUES (
    'Understanding Semiconductor Market Trends in 2025',
    'understanding-semiconductor-market-trends-2025',
    'An in-depth analysis of the current semiconductor market trends, key players, and future outlook for investors.',
    '<h2>Introduction</h2><p>The semiconductor industry continues to be a cornerstone of modern technology, powering everything from smartphones to artificial intelligence systems. In 2025, we are witnessing unprecedented changes in market dynamics.</p><h2>Key Trends</h2><ul><li><strong>AI Chip Demand:</strong> The explosion of AI applications has created massive demand for specialized chips.</li><li><strong>Supply Chain Resilience:</strong> Companies are diversifying manufacturing to reduce geopolitical risks.</li><li><strong>Automotive Revolution:</strong> Electric vehicles require significantly more semiconductors than traditional cars.</li></ul><h2>Market Leaders</h2><p>Companies like NVIDIA, AMD, Intel, and OnSemi are positioning themselves strategically to capture market share in high-growth segments.</p><h2>Investment Outlook</h2><p>Analysts predict continued growth in the sector, with particular strength in AI accelerators and power management solutions. However, cyclical nature of the industry requires careful timing.</p><h2>Conclusion</h2><p>The semiconductor market presents compelling opportunities for long-term investors who understand the technology trends and market cycles.</p>',
    NULL,
    1,
    'pending',
    '["Semiconductors","Technology","Market Analysis","Investment"]',
    0,
    NOW(),
    NOW()
);

-- Post 2: Pending - Different topic
INSERT INTO blog_posts (title, slug, excerpt, content, featured_image, user_id, status, tags, views, created_at, updated_at)
VALUES (
    'Top 5 Financial Ratios Every Investor Should Know',
    'top-5-financial-ratios-every-investor-should-know',
    'Learn the essential financial ratios that help you evaluate company performance and make informed investment decisions.',
    '<h2>Why Financial Ratios Matter</h2><p>Financial ratios are powerful tools that help investors quickly assess a company''s financial health, profitability, and efficiency. Here are the top 5 ratios you need to know.</p><h2>1. Price-to-Earnings (P/E) Ratio</h2><p>The P/E ratio shows how much investors are willing to pay for each dollar of earnings. A lower P/E might indicate undervaluation, while a higher P/E suggests growth expectations.</p><h2>2. Debt-to-Equity Ratio</h2><p>This measures financial leverage. A high ratio indicates more debt financing, which can amplify both gains and losses.</p><h2>3. Current Ratio</h2><p>Measures liquidity - the company''s ability to pay short-term obligations. Generally, a ratio above 1.0 is considered healthy.</p><h2>4. Return on Equity (ROE)</h2><p>Shows how efficiently a company generates profits from shareholders'' equity. Higher ROE typically indicates better management performance.</p><h2>5. Gross Margin</h2><p>Reveals how much profit a company makes after paying for cost of goods sold. Higher margins indicate pricing power and efficiency.</p><h2>Putting It All Together</h2><p>Use these ratios in combination, not isolation. Compare them to industry peers and historical trends for meaningful insights.</p>',
    NULL,
    1,
    'pending',
    '["Financial Analysis","Investing","Education","Ratios"]',
    0,
    NOW(),
    NOW()
);

-- Post 3: Pending - Company analysis
INSERT INTO blog_posts (title, slug, excerpt, content, featured_image, user_id, status, tags, views, created_at, updated_at)
VALUES (
    'OnSemi Q1 2025 Earnings Analysis: A Deep Dive',
    'onsemi-q1-2025-earnings-analysis',
    'Breaking down OnSemi''s latest quarterly results, examining revenue trends, margins, and future outlook.',
    '<h2>Executive Summary</h2><p>OnSemi reported Q1 2025 earnings with revenue of $1.45 billion, showing a sequential decline but maintaining strong profitability metrics.</p><h2>Revenue Performance</h2><p>Q1 revenue came in at $1,445.7 million, down 16% from Q4 2024. This reflects broader market softness in consumer electronics while automotive and industrial segments remained resilient.</p><h2>Margin Analysis</h2><p>Gross margin compressed to 20.3% from previous quarter''s higher levels. Operating expenses were well-controlled at $293.9 million, demonstrating management''s focus on efficiency.</p><h2>Segment Performance</h2><ul><li><strong>Automotive:</strong> Remained strong despite industry headwinds</li><li><strong>Industrial:</strong> Mixed results with pockets of weakness</li><li><strong>Consumer:</strong> Continued softness as expected</li></ul><h2>Management Outlook</h2><p>Management expects Q2 to show modest sequential improvement as inventory digestion continues. Full recovery anticipated in H2 2025.</p><h2>Valuation & Recommendation</h2><p>At current prices, OnSemi trades at attractive valuation multiples. Long-term investors should consider accumulating shares during this cyclical trough.</p>',
    NULL,
    1,
    'pending',
    '["OnSemi","Earnings","Company Analysis","Semiconductors"]',
    0,
    NOW(),
    NOW()
);
