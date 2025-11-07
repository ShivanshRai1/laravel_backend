import React, { useEffect, useState } from 'react';
import TableExportButtons from './TableExportButtons';

const UploadedDataTable = () => {
  const [data, setData] = useState([]);
  const [columns, setColumns] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchData = async () => {
      setLoading(true);
      setError(null);
      try {
        const res = await fetch('https://financial-dashboard-backend-7p3w.onrender.com/api/uploaded-financial-data');
        const rows = await res.json();
        setData(rows);
        if (rows.length > 0) {
          setColumns(Object.keys(rows[0]));
        }
      } catch (err) {
        setError('Failed to fetch uploaded data.');
      }
      setLoading(false);
    };
    fetchData();
  }, []);

  if (loading) return <div>Loading uploaded data...</div>;
  if (error) return <div style={{ color: 'red' }}>{error}</div>;
  if (!data.length) return <div>No uploaded data found.</div>;

  return (
    <div style={{ overflowX: 'auto', margin: '20px 0' }}>
      <h3>Uploaded Financial Data</h3>
      <div style={{ background: '#f5f5fa', padding: '8px 0 8px 0', marginBottom: 8, borderRadius: 6, display: 'flex', justifyContent: 'flex-end' }}>
        <TableExportButtons tableId="uploaded-data-table" filename="uploaded-data" data={data} columns={columns} />
      </div>
      <table id="uploaded-data-table" border="1" cellPadding="6" style={{ borderCollapse: 'collapse', minWidth: 600 }}>
        <thead>
          <tr>
            {columns.map(col => <th key={col}>{col}</th>)}
          </tr>
        </thead>
        <tbody>
          {data.map((row, idx) => (
            <tr key={idx}>
              {columns.map(col => <td key={col}>{row[col]}</td>)}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
};

export default UploadedDataTable;
