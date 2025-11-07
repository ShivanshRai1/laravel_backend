import React from 'react';
import { KEY_CHARTS_METRICS } from '../../utils/constants';

const ChartsTab = ({ 
  allCompaniesData, 
  selectedComparisonCompanies, 
  showTable, 
  toggleTable 
}) => {
  return (
    <div className="charts-tab">
      <div className="tab-header">
        <h2>📉 Key Financial Charts</h2>
        <p>Important financial metrics visualized over the last 4-5 years</p>
      </div>
      
      <div className="key-metrics-section">
        <h3>🎯 Top 5 Key Financial Metrics (4-5 Year Trends)</h3>
        
        <div className="key-charts-grid">
          {KEY_CHARTS_METRICS.map((metric) => {
            const metricKey = `key-chart-${metric}`;
            
            return (
              <div key={metricKey} className="key-metric-card">
                <div className="key-metric-header">
                  <h4>{metric}</h4>
                  <div className="metric-info">
                    <span className="time-range">2022-2025 Quarterly Data</span>
                  </div>
                </div>
                
                <div className="key-metric-content">
                  <div 
                    id={`chart-${metricKey}`} 
                    className="key-chart-container"
                    style={{ height: '400px' }}
                  ></div>
                  
                  <div className="chart-legend">
                    <div className="legend-companies">
                      {selectedComparisonCompanies.slice(0, 6).map((company, index) => (
                        <span 
                          key={company} 
                          className="legend-item"
                          style={{ 
                            borderLeft: `4px solid var(--company-color-${index})` 
                          }}
                        >
                          {company}
                        </span>
                      ))}
                    </div>
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      </div>
      
      <div className="charts-insights">
        <h3>📊 Market Insights</h3>
        <div className="insights-grid">
          <div className="insight-card">
            <h4>Revenue Growth Leaders</h4>
            <p>NVIDIA shows exceptional growth driven by AI demand, while traditional players like Intel face headwinds.</p>
          </div>
          <div className="insight-card">
            <h4>Profitability Champions</h4>
            <p>Companies with higher-margin products (GPU, specialized chips) demonstrate superior profit margins.</p>
          </div>
          <div className="insight-card">
            <h4>Financial Health</h4>
            <p>Strong balance sheets with low debt-to-equity ratios position companies well for future investments.</p>
          </div>
        </div>
      </div>
    </div>
  );
};

export default ChartsTab;