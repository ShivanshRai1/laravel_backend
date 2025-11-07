// Enhanced constants with all 10 companies and additional financial metrics for tabs

// List of all 10 companies (semiconductor and tech companies)
export const ALL_COMPANIES = [
  "NXP Semiconductors", "Onsemi", "TI", "Analog", "Vishay", "Infineon", 
  "Rohm", "Apple", "Microsoft", "Amazon"
];

// Company ticker symbols for stock data
export const COMPANY_TICKERS = {
  "nxp semiconductors": "NXPI",
  "onsemi": "ON",
  "ti": "TXN", 
  "analog": "ADI",
  "vishay": "VSH",
  "infineon": "IFX",
  "rohm": "ROHM",
  "apple": "AAPL",
  "microsoft": "MSFT", 
  "amazon": "AMZN"
};

// Financial metrics for different tab views
export const FINANCIALS_METRICS = [
  "Revenue",
  "Gross Profit", 
  "Operating Profit (EBIT)",
  "Net Profit/Income",
  "Total Assets",
  "Total Liabilities",
  "Total stockholders' equity",
  "Cash and cash equivalents"
];

export const INCOME_STATEMENT_METRICS = [
  "Revenue",
  "Cost of Goods Sold", 
  "Gross Profit",
  "Research and development (R&D)",
  "SG&A (incl. Marketing)",
  "Operating Profit (EBIT)",
  "Interest expense",
  "Interest income", 
  "Net Profit/Income"
];

export const KEY_CHARTS_METRICS = [
  "Revenue",
  "Net Profit Margin", 
  "ROE (Return on Equity)",
  "Gross Margin",
  "Debt-to-Equity Ratio"
];

// List of metrics to display in dashboard charts and tables
export const METRICS_TO_DISPLAY = [
  "Revenue",
  "Net Profit Margin",
  "Gross Margin",
  "Inventories as a % of Revenue",
  "COGS as % of Revenue",
  "SGM&A as % of Revenue",
  "R&D as % of Revenue",
  "Current Ratio",
  "Quick Ratio",
  "Debt-to-Equity Ratio",
  "ROE (Return on Equity)",
  "ROA (Return on Assets)",
];

// Colors assigned to companies for chart visualizations  
export const COMPANY_COLORS = [
  "#4889b5", "#ff9f1c", "#1a8c1a", "#c52324", "#8e5ea2", "#17becf",
  "#ff6b6b", "#4ecdc4", "#45b7d1", "#96ceb4"
];

// Metrics that are expressed as percentages
export const PERCENT_METRICS = [
  "Revenue QOQ",
  "Gross Margin",
  "Net Profit Margin",
  "ROE (Return on Equity)",
  "ROA (Return on Assets)",
  "Inventories as a % of Revenue",
  "COGS as % of Revenue",
  "SGM&A as % of Revenue",
  "R&D as % of Revenue"
];

// Metrics that are ratios (unitless)
export const RATIO_METRICS = [
  "Current Ratio",
  "Quick Ratio",
  "Debt-to-Equity Ratio"
];

// Metrics related to revenue
export const REVENUE_METRICS = [
  "Revenue"
];

// Definitions for multi-metric percent charts (used for dual-axis charts)
export const MULTI_METRIC_PERCENT_CHARTS = [
  {
    percentMetric: "Inventories as a % of Revenue",
    absoluteMetric: "Inventories",
    label: "Inventories"
  },
  {
    percentMetric: "COGS as % of Revenue",
    absoluteMetric: "Cost of Goods Sold",
    label: "Cost of Goods Sold"
  },
  {
    percentMetric: "SGM&A as % of Revenue",
    absoluteMetric: "SG&A (incl. Marketing)",
    label: "SG&A (incl. Marketing)"
  },
  {
    percentMetric: "R&D as % of Revenue",
    absoluteMetric: "Research and development (R&D)",
    label: "Research and development (R&D)"
  }
];

// Color schemes for different chart types
export const CHART_COLOR_SCHEMES = {
  "revenue-margins": ["#4889b5", "#ff9800", "#c52324"],
  "roe-roa": ["#8c5b66", "#5c6f91"],
  "multi-metric": ["#4889b5", "#87ceeb", "#c52324"]
};





// Tab configuration for the dashboard
export const DASHBOARD_TABS = [
  { id: 'overview', label: 'Overview', icon: '📊' },
  { id: 'financials', label: 'Financials', icon: '💰' },
  { id: 'income_statement', label: 'Income Statement', icon: '📈' },
  { id: 'news_analysis', label: 'News & Analysis', icon: '📰' },
  { id: 'charts', label: 'Charts', icon: '📉' }
];

// Time periods for data display
export const TIME_PERIODS = [
  "CY 2025 Q1", "CY 2024 Q4", "CY 2024 Q3", "CY 2024 Q2", "CY 2024 Q1",
  "CY 2023 Q4", "CY 2023 Q3", "CY 2023 Q2", "CY 2023 Q1", 
  "CY 2022 Q4", "CY 2022 Q3", "CY 2022 Q2"
];

// Default comparison companies function
export function getDefaultCompanies(allCompanies) {
  return allCompanies.slice(0, 6); // First 6 companies as default
}

// Descriptions and formulas for key financial metrics (used in tooltips/info buttons)
export const METRIC_INFO = {
  "Current Ratio": {
    description: "The Current ratio measures a company's ability to pay off short-term liabilities with current assets. A ratio of 2.0 means the company has $2 in current assets for every $1 in current liabilities. Higher values generally indicate stronger liquidity, though excessively high ratios may suggest inefficient cash management.",
    formula: "Current Assets ÷ Current Liabilities"
  },
  "Quick Ratio": {
    description: "Quick Ratio measures immediate liquidity by excluding inventory from current assets. A ratio of 1.5 means the company can cover $1.50 of short-term debt for every $1 owed using only liquid assets (cash, receivables). Values above 1.0 indicate ability to meet short-term obligations without relying on inventory sales.",
    formula: "(Current Assets - Inventory) ÷ Current Liabilities"
  },
  "Debt-to-Equity Ratio": {
    description: "Debt-to-Equity Ratio shows financial leverage by comparing total debt to shareholders' equity. A ratio of 0.4 means the company has $0.40 in debt for every $1 in equity. Lower ratios indicate less financial leverage and risk, while moderate leverage can enhance returns in capital-intensive industries like semiconductors.",
    formula: "Total Debt ÷ Total Shareholders' Equity"
  },
  "ROE (Return on Equity)": {
    description: "ROE measures how effectively a company generates profit from shareholders' equity. A 15% ROE means the company generates $0.15 profit for every $1 of shareholder equity invested. Higher percentages indicate more efficient use of shareholder capital, though very high ROE may indicate excessive leverage.",
    formula: "Net Income ÷ Average Shareholders' Equity × 100"
  },
  "ROA (Return on Assets)": {
    description: "ROA measures how efficiently a company uses its total assets to generate profit. A 10% ROA means the company generates $0.10 profit for every $1 in total assets. Higher percentages indicate better operational efficiency and asset management effectiveness.",
    formula: "Net Income ÷ Average Total Assets × 100"
  }
};

export const METRIC_FORMULAS = {
  "Current Ratio": {
    description: METRIC_INFO["Current Ratio"].description,
    formula: "Current Ratio = Current Assets / Current Liabilities"
  },
  "Quick Ratio": {
    description: METRIC_INFO["Quick Ratio"].description,
    formula: "Quick Ratio = (Cash + Receivables) / Current Liabilities"
  },
  "Debt-to-Equity Ratio": {
    description: METRIC_INFO["Debt-to-Equity Ratio"].description,
    formula: "Debt-to-Equity Ratio = Total Liabilities / Shareholders’ Equity"
  },
  "ROA (Return on Assets)": {
    description: METRIC_INFO["ROA (Return on Assets)"].description,
    formula: "ROA (Return on Assets) = Net Profit / Total Assets"
  },
  "ROE (Return on Equity)": {
    description: METRIC_INFO["ROE (Return on Equity)"].description,
    formula: "ROE (Return on Equity) = Net Profit / Shareholders’ Equity"
  }
};