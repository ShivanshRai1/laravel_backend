import React, { useEffect, useState } from "react";
import UploadedDataTable from "../components/UploadedDataTable";
import {
  drawChart,
  drawComparisonChart,
  drawMultiMetricChart,
} from "../ChartComponent";
import { transformSQLData } from "../utils/api";
import { CHART_COLOR_SCHEMES, METRICS_TO_DISPLAY, COMPANY_TICKERS } from "../utils/constants";

const API_BASE_URL = "https://financial-dashboard-backend-7p3w.onrender.com";

const UploadedCharts = () => {
  const [companyData, setCompanyData] = useState({});
  const [companyList, setCompanyList] = useState([]);
  const [activeCompany, setActiveCompany] = useState("");
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchData = async () => {
      setLoading(true);
      setError(null);
      try {
        const res = await fetch(`${API_BASE_URL}/api/uploaded-financial-data`);
        if (!res.ok) throw new Error(`API error: ${res.status}`);
        const rows = await res.json();
        const data = await transformSQLData(rows);
        if (!data || typeof data !== 'object') throw new Error('Uploaded data is not in the expected format.');
        setCompanyData(data);
        const companies = Object.keys(data);
        setCompanyList(Array.isArray(companies) ? companies : []);
        setActiveCompany(Array.isArray(companies) && companies.length > 0 ? companies[0] : "");
      } catch (err) {
        setError(err.message || "Failed to load uploaded data.");
      }
      setLoading(false);
    };
    fetchData();
  }, []);

  useEffect(() => {
    try {
      if (!activeCompany || !companyData || typeof companyData !== 'object' || !companyData[activeCompany] || typeof companyData[activeCompany] !== 'object' || !companyData[activeCompany].metrics || typeof companyData[activeCompany].metrics !== 'object') return;
      // Draw charts for the selected company (reuse Dashboard logic)
      const data = companyData[activeCompany];
      if (!Array.isArray(METRICS_TO_DISPLAY)) return;
      METRICS_TO_DISPLAY.forEach((metric, idx) => {
        const chartId = `uploaded-chart-${metric.replace(/\s+/g, "-")}`;
        const quarters = Array.isArray(data.quarters) ? data.quarters : [];
        let values = (data.metrics && Array.isArray(data.metrics[metric])) ? data.metrics[metric] : [];
        // Filter out undefined/null values and corresponding quarters
        const filtered = values
          .map((v, i) => ({ v, q: quarters[i] }))
          .filter(item => item.v !== undefined && item.v !== null && !isNaN(item.v));
        if (filtered.length > 0) {
          const filteredValues = filtered.map(item => item.v);
          const filteredQuarters = filtered.map(item => item.q);
          drawChart(
            chartId,
            "line",
            metric,
            filteredQuarters,
            filteredValues,
            CHART_COLOR_SCHEMES["default"][0] || "#607d8b",
            { title: metric }
          );
        }
      });
    } catch (err) {
      setError("Chart rendering error: " + (err.message || err));
    }
  }, [activeCompany, companyData]);

  if (loading) return <div>Loading uploaded charts...</div>;
  if (error) return <div style={{ color: 'red' }}>Error: {error}</div>;
  if (!Array.isArray(companyList) || !companyList.length) return <div>No uploaded data found for charts.</div>;
  if (!activeCompany || !companyData[activeCompany] || !companyData[activeCompany].metrics) {
    return <div style={{ color: 'red' }}>Uploaded data format is invalid or missing required fields.</div>;
  }

  return (
    <div style={{ padding: 32 }}>
      <h1>Uploaded Data Charts</h1>
      <div style={{ marginBottom: 24 }}>
        <label>Select Company: </label>
        <select value={activeCompany} onChange={e => setActiveCompany(e.target.value)}>
          {companyList.map(company => (
            <option key={company} value={company}>{company}</option>
          ))}
        </select>
      </div>
      {METRICS_TO_DISPLAY.map(metric => (
        <div key={metric} style={{ marginBottom: 40 }}>
          <h3>{metric}</h3>
          <canvas id={`uploaded-chart-${metric.replace(/\s+/g, "-")}`} height="300" style={{ width: "100%", maxWidth: 900 }}></canvas>
        </div>
      ))}
      <div style={{ marginTop: 40 }}>
        <UploadedDataTable />
      </div>
    </div>
  );
};

export default UploadedCharts;
