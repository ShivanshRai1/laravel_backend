import { PERCENT_METRICS, RATIO_METRICS, REVENUE_METRICS } from "./constants";

// Returns the unit label for a given metric (used for axis labels, etc.)
export function getMetricUnit(metric) {
  if (PERCENT_METRICS.includes(metric)) return "In %"; // Percentage metrics
  if (RATIO_METRICS.includes(metric)) return ""; // Ratio metrics have no unit
  if (REVENUE_METRICS.includes(metric)) return "In Millions USD"; // Revenue metrics
  return "In Millions USD"; // Default to USD if unknown
}

// Formats a value for display in a table cell, depending on metric type
export function formatTableValue(metric, value, isComparison = false) {
  // If value is missing or not a number, show empty cell
  if (value === null || value === undefined || isNaN(value)) return "";

  // List of metrics that should be formatted as USD
  const USD_METRICS = [
    "Revenue",
    "Inventories",
    "Cost of Goods Sold",
    "SG&A (incl. Marketing)",
    "Research and development (R&D)",
  ];

  // Format percentage metrics
  if (PERCENT_METRICS.includes(metric)) {
    return isComparison
      ? `${Math.round(value)}%` // Round for comparison tables
      : `${value.toFixed(1)}%`; // One decimal for detail tables
  }

  // Format ratio metrics (unitless)
  if (RATIO_METRICS.includes(metric)) {
    return isComparison
      ? `${Math.round(value * 100) / 100}` // Round to two decimals for comparison
      : `${value.toFixed(2)}`; // Always two decimals for detail
  }

  // Format USD metrics
  if (USD_METRICS.includes(metric)) {
    return isComparison
      ? `$${Math.round(value)}M` // Round for comparison
      : `$${value.toFixed(1)}M`; // One decimal for detail
  }

  // Default formatting for other metrics
  return isComparison
    ? `${Math.round(value)}`
    : `${value.toFixed(1)}`;
}