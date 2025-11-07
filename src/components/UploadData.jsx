import React, { useState } from 'react';
import axios from 'axios';

const UploadData = () => {
  const [file, setFile] = useState(null);
  const [status, setStatus] = useState('');
  const [invalidRows, setInvalidRows] = useState([]);

  const handleFileChange = (e) => {
    setFile(e.target.files[0]);
  };

  const handleUpload = async () => {
    if (!file) return;
    const formData = new FormData();
    formData.append('file', file);
    setInvalidRows([]);
    try {
      setStatus('Uploading...');
      // Always use the production backend URL for uploads
      const response = await axios.post('https://financial-dashboard-backend-7p3w.onrender.com/api/upload', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      if (response.data && response.data.invalidRows && response.data.invalidRows.length > 0) {
        setInvalidRows(response.data.invalidRows);
        setStatus('Upload successful, but some rows were invalid.');
      } else {
        setStatus('Upload successful!');
      }
    } catch (err) {
      if (err.response && err.response.data && err.response.data.invalidRows) {
        setInvalidRows(err.response.data.invalidRows);
        setStatus('Upload failed: some or all rows were invalid.');
      } else {
        setStatus('Upload failed.');
      }
    }
  };

  return (
    <div>
      <h2>Upload Financial Data</h2>
      <input type="file" accept=".csv,.xlsx" onChange={handleFileChange} />
      <button onClick={handleUpload}>Upload</button>
      <p>{status}</p>
      {invalidRows.length > 0 && (
        <div style={{ color: 'red', marginTop: 10 }}>
          <strong>Invalid Rows:</strong>
          <ul style={{ maxHeight: 200, overflowY: 'auto', fontSize: 13 }}>
            {invalidRows.map((row, idx) => (
              <li key={idx}>
                {row.error}
                <br />
                <span style={{ color: '#555' }}>{JSON.stringify(row.row)}</span>
              </li>
            ))}
          </ul>
        </div>
      )}
    </div>
  );
};

export default UploadData;
