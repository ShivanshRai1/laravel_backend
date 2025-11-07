import React from 'react';

const NewsAnalysisTab = ({ selectedComparisonCompanies }) => {
  const mockNewsData = [
    {
      id: 1,
      title: "NVIDIA's AI Chip Demand Continues to Soar",
      summary: "Strong data center revenue driven by AI workload acceleration",
      company: "NVIDIA",
      date: "Oct 18, 2025",
      sentiment: "positive",
      impact: "high"
    },
    {
      id: 2,
      title: "AMD Gains Market Share in Server Processors",
      summary: "EPYC processors showing strong adoption in cloud computing",
      company: "AMD",
      date: "Oct 17, 2025",
      sentiment: "positive", 
      impact: "medium"
    },
    {
      id: 3,
      title: "Intel's Manufacturing Investments Show Promise",
      summary: "New fab construction progressing ahead of schedule",
      company: "Intel",
      date: "Oct 16, 2025",
      sentiment: "neutral",
      impact: "medium"
    },
    {
      id: 4,
      title: "Semiconductor Industry Faces Supply Chain Challenges",
      summary: "Global chip shortage continues to impact production timelines",
      company: "Industry",
      date: "Oct 15, 2025",
      sentiment: "negative",
      impact: "high"
    },
    {
      id: 5,
      title: "Broadcom's Software Revenue Drives Growth",
      summary: "Enterprise software segment outperforms semiconductor division",
      company: "Broadcom",
      date: "Oct 14, 2025",
      sentiment: "positive",
      impact: "medium"
    }
  ];

  const marketAnalysis = [
    {
      category: "AI & Machine Learning",
      trend: "🚀 Explosive Growth",
      description: "AI chip demand driving unprecedented revenue for GPU manufacturers",
      leaders: ["NVIDIA", "AMD"]
    },
    {
      category: "Automotive Semiconductors", 
      trend: "📈 Steady Growth",
      description: "Electric vehicle adoption fueling demand for power management ICs",
      leaders: ["Infineon", "Onsemi", "TI"]
    },
    {
      category: "5G Infrastructure",
      trend: "📊 Moderate Growth", 
      description: "Continued rollout supporting RF and communication chip demand",
      leaders: ["Broadcom", "Analog"]
    },
    {
      category: "Industrial IoT",
      trend: "🔄 Cyclical Recovery",
      description: "Factory automation driving sensor and control chip adoption", 
      leaders: ["TI", "Analog", "Vishay"]
    }
  ];

  return (
    <div className="news-analysis-tab">
      <div className="tab-header">
        <h2>📰 News & Market Analysis</h2>
        <p>Latest industry news, trends, and market insights</p>
      </div>
      
      <div className="content-grid">
        <div className="news-section">
          <h3>📢 Latest News</h3>
          <div className="news-list">
            {mockNewsData.map((news) => (
              <div key={news.id} className="news-item">
                <div className="news-header">
                  <div className="news-meta">
                    <span className={`company-tag ${news.company.toLowerCase()}`}>
                      {news.company}
                    </span>
                    <span className="news-date">{news.date}</span>
                  </div>
                  <div className="news-indicators">
                    <span className={`sentiment ${news.sentiment}`}>
                      {news.sentiment === 'positive' ? '📈' : 
                       news.sentiment === 'negative' ? '📉' : '➡️'}
                    </span>
                    <span className={`impact ${news.impact}`}>
                      {news.impact === 'high' ? '🔥' : '⚡'}
                    </span>
                  </div>
                </div>
                <h4 className="news-title">{news.title}</h4>
                <p className="news-summary">{news.summary}</p>
              </div>
            ))}
          </div>
        </div>
        
        <div className="analysis-section">
          <h3>📊 Market Trend Analysis</h3>
          <div className="trends-list">
            {marketAnalysis.map((trend, index) => (
              <div key={index} className="trend-item">
                <div className="trend-header">
                  <h4>{trend.category}</h4>
                  <span className="trend-indicator">{trend.trend}</span>
                </div>
                <p className="trend-description">{trend.description}</p>
                <div className="trend-leaders">
                  <strong>Market Leaders:</strong> {trend.leaders.join(', ')}
                </div>
              </div>
            ))}
          </div>
          
          <div className="market-outlook">
            <h4>🎯 Market Outlook</h4>
            <div className="outlook-grid">
              <div className="outlook-item bullish">
                <h5>📈 Bullish Factors</h5>
                <ul>
                  <li>AI and ML adoption acceleration</li>
                  <li>Electric vehicle market expansion</li>
                  <li>5G infrastructure buildout</li>
                  <li>Industrial automation growth</li>
                </ul>
              </div>
              <div className="outlook-item bearish">
                <h5>📉 Risk Factors</h5>
                <ul>
                  <li>Supply chain disruptions</li>
                  <li>Geopolitical trade tensions</li>
                  <li>Cyclical demand patterns</li>
                  <li>Inventory overcorrection</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default NewsAnalysisTab;