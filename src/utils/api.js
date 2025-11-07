import { COMPANY_TICKERS } from "./constants";
import { extractTickerFromName, sortQuarters } from "./helpers";

// Cache for dynamically resolved tickers to avoid repeated lookups
const dynamicTickerCache = {};
const TICKER_CACHE_DURATION = 24 * 60 * 60 * 1000; // 1 day

// Attempts to get a company's stock ticker using several strategies
export async function getCompanyTicker(companyName) {
  if (!companyName) return "";
  const key = companyName.trim().toLowerCase();

  // First, check static mapping
  if (COMPANY_TICKERS[key]) return COMPANY_TICKERS[key];

  // Next, check cache for previously resolved ticker
  if (dynamicTickerCache[key] && (Date.now() - dynamicTickerCache[key].timestamp < TICKER_CACHE_DURATION)) {
    return dynamicTickerCache[key].ticker;
  }

  // Try to extract ticker from company name string
  const extracted = extractTickerFromName(companyName);
  if (extracted) return extracted;

  // If all else fails, return empty string
  return "";
}

// Fetches live stock price data for a ticker symbol
export async function fetchStockData(ticker) {
  try {
    const baseUrl = import.meta.env.VITE_API_URL;
    const url = `${baseUrl}/api/stock-price/${encodeURIComponent(ticker)}`;
    const response = await fetch(url);
    if (!response.ok) return null;
    // Ensure no sensitive info is present
    const data = await response.json();
    // Twelve Data returns { price: "123.45" } or { code: ..., message: ... }
    if (data.price) {
      return {
        symbol: ticker,
        price: parseFloat(data.price),
        lastUpdated: new Date().toLocaleDateString()
      };
    } else {
      return null;
    }
  } catch (error) {
    return null;
  }
}

// Transforms SQL rows into dashboard-friendly company data structure
export async function transformSQLData(rows) {
  const companies = {};
  if (!rows.length) return companies;

  // Get unique company and metric names from data
  const companyNames = Array.from(new Set(rows.map(row => row.Company).filter(Boolean)));
  const metricNames = Array.from(new Set(rows.map(row => row.Metrics).filter(Boolean)));

  // Find all quarter columns and sort them chronologically
  let quarterCols = Object.keys(rows[0]).filter(key => key.match(/Q\d/));
  quarterCols = sortQuarters(quarterCols);

  // Resolve tickers for each company (async)
  const tickerPromises = companyNames.map(async company => {
    if (company.trim().toLowerCase() === "company") return [company, null];
    const ticker = await getCompanyTicker(company);
    return [company, ticker];
  });
  const tickerResults = await Promise.all(tickerPromises);
  const tickerMap = Object.fromEntries(tickerResults);

  // Build company data objects
  companyNames.forEach(company => {
    if (company.trim().toLowerCase() === "company") return;
    const companyRows = rows.filter(row => row.Company === company);
    const metrics = {};

    // For each metric, collect values for all quarters
    metricNames.forEach(metric => {
      const metricRow = companyRows.find(row => row.Metrics && row.Metrics.trim() === metric.trim());
      if (metricRow) {
        metrics[metric] = quarterCols.map(q => {
          // Remove percent, spaces, and commas before parsing
          const val = (metricRow[q] || "").toString().replace(/[%\s,]/g, "");
          return val ? parseFloat(val) : null;
        });
      }
    });

    // Store company data including quarters, metrics, and ticker
    companies[company] = {
      quarters: quarterCols,
      metrics,
      ticker: tickerMap[company] || ""
    };
  });

  return companies;
}

// Returns the default companies to compare (first two in list)
export function getDefaultCompanies(allCompanies) {
  return allCompanies.slice(0, 2);
}