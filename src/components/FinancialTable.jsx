import React from 'react';
import TableExportButtons from './TableExportButtons';
import { formatTableValue } from '../utils/formatters';
import './FinancialTable.css';

const FinancialTable = ({ 
  allCompaniesData, 
  selectedCompanies, 
  metric, 
  tableId 
}) => {
  if (!selectedCompanies || selectedCompanies.length === 0) {
    return (
      <div className="no-data-message">
        <p>Please select companies to view data</p>
      </div>
    );
  }

  // Get the first company's quarters for table headers
  const firstCompany = selectedCompanies[0];
  const firstCompanyData = allCompaniesData[firstCompany];
  
  if (!firstCompanyData || !firstCompanyData.quarters) {
    return (
      <div className="no-data-message">
        <p>No quarter data available</p>
      </div>
    );
  }

  const quarters = firstCompanyData.quarters;

  return (
    <div className="financial-table-container" id={`table-${tableId}`}>
      <div style={{ background: '#f5f5fa', padding: '8px 0 8px 0', marginBottom: 8, borderRadius: 6, display: 'flex', justifyContent: 'flex-end' }}>
        <TableExportButtons tableId={`table-${tableId}`} filename={metric || 'financial-table'} />
      </div>
      <table className="financial-table">
        <thead>
          <tr>
            <th className="company-header">Company</th>
            {quarters.map(quarter => (
              <th key={quarter} className="quarter-header">{quarter}</th>
            ))}
          </tr>
        </thead>
        <tbody>
          {selectedCompanies.map((company, index) => {
            const companyData = allCompaniesData[company];
            if (!companyData || !companyData.metrics || !companyData.metrics[metric]) {
              return (
                <tr key={company}>
                  <td className="company-name">{company}</td>
                  {quarters.map((_, qIndex) => (
                    <td key={qIndex} className="no-data">—</td>
                  ))}
                </tr>
              );
            }

            const values = companyData.metrics[metric];
            
            return (
              <tr key={company} className={index % 2 === 0 ? 'even-row' : 'odd-row'}>
                <td className="company-name">
                  <span 
                    className="company-indicator"
                    style={{ 
                      backgroundColor: `var(--company-color-${index})`,
                      borderColor: `var(--company-color-${index})`
                    }}
                  ></span>
                  {company}
                </td>
                {values.map((value, qIndex) => (
                  <td key={qIndex} className="value-cell">
                    {formatTableValue(metric, value, true)}
                  </td>
                ))}
              </tr>
            );
          })}
        </tbody>
      </table>
    </div>
  );
};

export default FinancialTable;