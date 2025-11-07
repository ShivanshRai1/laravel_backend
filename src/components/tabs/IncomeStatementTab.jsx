import React from 'react';
import FinancialTable from '../FinancialTable';
import { INCOME_STATEMENT_METRICS } from '../../utils/constants';

const IncomeStatementTab = ({ 
  allCompaniesData, 
  selectedComparisonCompanies, 
  showTable, 
  toggleTable 
}) => {
  return (
    <div className="income-statement-tab">
      <div className="tab-header">
        <h2>📈 Income Statement Analysis</h2>
        <p>Revenue, expenses, and profitability metrics across quarters</p>
      </div>
      
      <div className="metrics-grid">
        {INCOME_STATEMENT_METRICS.map((metric) => {
          const metricKey = `income-${metric}`;
          
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

export default IncomeStatementTab;