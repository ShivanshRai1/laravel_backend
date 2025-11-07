const express = require('express');
const multer = require('multer');
const path = require('path');
const fs = require('fs');
const { parse } = require('csv-parse');
const mysql = require('mysql2');

const router = express.Router();

const dbConfig = {
  host: 'mysql-27aceb02-dashboard01.k.aivencloud.com',
  port: 26127,
  user: 'avnadmin',
  password: process.env.DB_PASSWORD,
  database: 'financial_dashboard',
  ssl: {
    ca: fs.readFileSync(path.join(__dirname, 'ca.pem')),
  },
};
const connection = mysql.createConnection(dbConfig);

const uploadsDir = path.join(__dirname, 'uploads');
if (!fs.existsSync(uploadsDir)) {
  fs.mkdirSync(uploadsDir);
}
const upload = multer({ dest: uploadsDir });

router.post('/', upload.single('file'), (req, res) => {
  if (!req.file) {
    return res.status(400).json({ error: 'No file uploaded' });
  }
  const filePath = req.file.path;
  const results = [];
  const invalidRows = [];

  fs.createReadStream(filePath)
    .pipe(parse({ columns: true, skip_empty_lines: true, trim: true }))
    .on('data', (row) => {
      // Clean and validate row: trim whitespace, check for missing/invalid values
      const cleanedRow = {};
      let isValid = true;
      let errorMsg = [];
      Object.entries(row).forEach(([key, value]) => {
        const trimmedKey = key ? key.trim() : '';
        let trimmedValue = typeof value === 'string' ? value.trim() : value;
        // Example: treat empty string or null as missing
        if (trimmedKey && (trimmedValue === '' || trimmedValue === null || trimmedValue === undefined)) {
          isValid = false;
          errorMsg.push(`Missing value for column: ${trimmedKey}`);
        }
        cleanedRow[trimmedKey] = trimmedValue;
      });
      // Add more validation rules as needed (e.g., numeric columns, date format)
      // Example: if (cleanedRow['Amount'] && isNaN(Number(cleanedRow['Amount']))) { ... }
      if (isValid) {
        results.push(cleanedRow);
      } else {
        invalidRows.push({ row: cleanedRow, error: errorMsg.join('; ') });
      }
    })
    .on('end', () => {
      fs.unlinkSync(filePath);
      if (results.length === 0) {
        return res.status(400).json({ success: false, message: 'No valid data found in CSV.', invalidRows });
      }
      // Get columns from first row, filter out empty columns
      let columns = Object.keys(results[0]).filter(col => col && col.trim() !== '');
      // Wrap each column name in backticks
      const escapedColumns = columns.map(col => `\`${col}\``);
      const placeholders = columns.map(() => '?').join(',');
      const insertSQL = `INSERT INTO uploaded_financial_data (${escapedColumns.join(',')}) VALUES (${placeholders})`;

      const insertPromises = results.map((row, idx) => {
        const values = columns.map(col => row[col]);
        return new Promise((resolve, reject) => {
          connection.query(insertSQL, values, (err, result) => {
            if (err) {
              console.error(`DB insert error at row ${idx}:`, err, row);
              reject(err);
            } else {
              resolve(result);
            }
          });
        });
      });

      Promise.all(insertPromises)
        .then(() => {
          res.json({ success: true, message: 'Data uploaded and stored successfully.', invalidRows });
        })
        .catch((err) => {
          console.error('Database insert error:', err);
          res.status(500).json({ success: false, message: 'Database insert error', error: err.message });
        });
    })
    .on('error', (err) => {
      fs.unlinkSync(filePath);
      console.error('CSV read/parse error:', err);
      res.status(500).json({ error: 'Failed to read or parse uploaded file.', details: err.message });
    });
});

module.exports = router;