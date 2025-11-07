import React from 'react';
import FinancialTable from '../FinancialTable';
import { FINANCIALS_METRICS } from '../../utils/constants';

const FinancialsTab = ({ 
  allCompaniesData, 
  selectedComparisonCompanies, 
  showTable, 
  toggleTable 
}) => {
  return (
    <div className="financials-tab">
      <div className="tab-header">
        <h2>📰 Financial Overview</h2>
        <p>Key financial metrics and balance sheet data for semiconductor companies</p>
      </div>
      
      <div className="metrics-grid">
        {FINANCIALS_METRICS.map((metric) => {
          const metricKey = `financials-${metric}`;
          
          return (
            <div key={metricKey} className="metric-card">
              <div className="metric-header">
                <h3>{metric}</h3>
                <button 
                  className="toggle-table-btn"
                  onClick={() => toggleTable(metricKey)}
                >
                  {showTable[metricKey] ? '📊 Show Chart' : '📋 Show Table'}
                </button>
              </div>
              
              <div className="metric-content">
                <div 
                  id={`chart-${metricKey}`} 
                  className="chart-container"
                  style={{ 
                    display: showTable[metricKey] ? 'none' : 'block',
                    height: '300px'
                  }}
                ></div>
                
                {showTable[metricKey] && (
                  <FinancialTable
                    allCompaniesData={allCompaniesData}
                    selectedCompanies={selectedComparisonCompanies}
                    metric={metric}
                    tableId={metricKey}
                  />
                )}
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
};

export default FinancialsTab;