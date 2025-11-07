require('dotenv').config();
const express = require('express');
const app = express();
const PORT = process.env.PORT || 5000;
const mysql = require('mysql2');
const fs = require('fs');
const cors = require('cors');
const path = require('path');
const uploadRouter = require('./upload');

// CORS setup
const allowedOrigins = [
  'https://financial-dashboard-frontend-wttu.onrender.com',
  'http://localhost:5173',
  'http://127.0.0.1:5173',
];
app.use(cors({
  origin: function (origin, callback) {
    // allow requests with no origin (like mobile)
    if (!origin) return callback(null, true);
    if (allowedOrigins.indexOf(origin) !== -1) {
      callback(null, true);
    } else {
      callback(new Error('Not allowed by CORS'));
    }
  },
  credentials: true,
}));
app.use(express.json());


// API: Fetch and log data from EDGAR
const fetch = (...args) => import('node-fetch').then(({default: fetch}) => fetch(...args));
app.get('/api/edgar', async (req, res) => {
  try {
    // Example: Fetch Onsemi filings (CIK 0001097864)
    const edgarUrl = 'https://data.sec.gov/submissions/CIK0001097864.json';
    const response = await fetch(edgarUrl, {
      headers: {
        'User-Agent': 'DashboardApp/1.0 (raishivansh123@gmail.com)'
      }
    });
    const data = await response.json();
    console.log('EDGAR API data:', data);
    res.json({ status: 'success', message: 'Data logged to server console.' });
  } catch (err) {
    console.error('Failed to fetch EDGAR data:', err);
    res.status(500).json({ error: 'Failed to fetch EDGAR data' });
  }
});

// Debug: print loaded DB password (masked)
if (!process.env.DB_PASSWORD) {
  console.error('DB_PASSWORD is not loaded from .env!');
} else {
  console.log('Loaded DB_PASSWORD:', process.env.DB_PASSWORD.slice(0,4) + '...' + process.env.DB_PASSWORD.slice(-4));
}

const connection = mysql.createConnection({
  host: 'mysql-27aceb02-dashboard01.k.aivencloud.com',
  port: 26127,
  user: 'avnadmin',
  password: process.env.DB_PASSWORD,
  database: 'financial_dashboard',
  ssl: {
    ca: fs.readFileSync(path.join(__dirname, 'ca.pem')),
  },
});

connection.connect((err) => {
  if (err) {
    console.error('Failed to connect to MySQL:', err.message);
  } else {
    console.log('Connected to MySQL database successfully.');
  }
});


// API: Get all financial data (legacy)
app.get('/api/financial-data', (req, res) => {
  connection.query('SELECT * FROM financial_data', (err, results) => {
    if (err) {
      console.error('Database error:', err);
      return res.status(500).json({ error: 'Database error' });
    }
    res.json(results);
  });
});

// API: Get all uploaded financial data (new)
app.get('/api/uploaded-financial-data', (req, res) => {
  connection.query('SELECT * FROM uploaded_financial_data', (err, results) => {
    if (err) {
      console.error('Database error:', err);
      return res.status(500).json({ error: 'Database error' });
    }
    res.json(results);
  });
});

// API: Get live stock price from Twelve Data
app.get('/api/stock-price/:ticker', async (req, res) => {
  const ticker = req.params.ticker;
  const apiKey = process.env.TWELVE_DATA_API_KEY || '73491e879df54ff7967dd39d1c3f3c77';
  const url = `https://api.twelvedata.com/price?symbol=${encodeURIComponent(ticker)}&apikey=${apiKey}`;
  try {
    const response = await fetch(url);
    const data = await response.json();
    res.json(data);
  } catch (err) {
    res.status(500).json({ error: 'Failed to fetch Twelve Data price' });
  }
});

app.use('/api/upload', uploadRouter);
app.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
});