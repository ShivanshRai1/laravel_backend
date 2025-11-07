import React, { Component } from "react";

import "../App.css";
import {
  drawChart,
  drawComparisonChart,
  drawMultiMetricChart,
} from "../ChartComponent.js";

import InfoButton from "../components/InfoButton";
import TabNavigation from "../components/TabNavigation";
import FinancialsTab from "../components/tabs/FinancialsTab";
import IncomeStatementTab from "../components/tabs/IncomeStatementTab";
import ChartsTab from "../components/tabs/ChartsTab";
import NewsAnalysisTab from "../components/tabs/NewsAnalysisTab";
import "../components/tabs/TabStyles.css";

import {
  METRICS_TO_DISPLAY,
  RATIO_METRICS,
  MULTI_METRIC_PERCENT_CHARTS,
  CHART_COLOR_SCHEMES,
  COMPANY_TICKERS,
  METRIC_INFO,
  METRIC_FORMULAS,
  ALL_COMPANIES,
  DASHBOARD_TABS,
  FINANCIALS_METRICS,
  INCOME_STATEMENT_METRICS,
  KEY_CHARTS_METRICS
} from "../utils/constants";
import { getMetricUnit, formatTableValue } from "../utils/formatters";
import { getCompanyColor } from "../utils/helpers";
import { fetchStockData, transformSQLData, getDefaultCompanies } from "../utils/api";
import { generateMockFinancialData, mergeWithExistingData } from "../utils/mockFinancialData";

// Only declare these variables once
const stockDataCache = {};
const CACHE_DURATION = 60000; // 1 minute cache

class Dashboard extends Component {
  constructor(props) {
    super(props);
    this.state = {
      allCompaniesData: {},
      activeSection: "comparison",
      activeTab: "overview", // New tab state
      showTable: {},
      defaultComparisonCompanies: [],
      selectedComparisonCompanies: [],
      showAllMetricsTable: {},
      stockData: {}
    };
    this.updateComparison = this.updateComparison.bind(this);
    this.handleNavClick = this.handleNavClick.bind(this);
    this.handleTabChange = this.handleTabChange.bind(this);
    this.toggleTable = this.toggleTable.bind(this);
    this.handleCompanyCheckbox = this.handleCompanyCheckbox.bind(this);
    this.fetchLiveStockData = this.fetchLiveStockData.bind(this);
  }

  // Fetch live stock price for a ticker, using cache if available
  async fetchLiveStockData(ticker) {
    const cachedData = stockDataCache[ticker];
    if (cachedData && Date.now() - cachedData.timestamp < CACHE_DURATION) {
      return cachedData.data;
    }
    const stockData = await fetchStockData(ticker);
    if (stockData) {
      stockDataCache[ticker] = {
        data: stockData,
        timestamp: Date.now()
      };
      this.setState(prevState => ({
        stockData: {
          ...prevState.stockData,
          [ticker]: stockData
        }
      }));
    }
    return stockData;
  }

  // Load stock data for a company if it has a ticker
  async loadStockDataForCompany(company) {
    const data = this.state.allCompaniesData[company];
    if (data && data.ticker && COMPANY_TICKERS[company.toLowerCase()]) {
      await this.fetchLiveStockData(data.ticker);
    }
  }

  // Fetch all company and component data when the app loads
  async componentDidMount() {
    try {
      const API_BASE_URL = "https://financial-dashboard-backend-7p3w.onrender.com";
      // Fetch main financial data only
      const res = await fetch(`${API_BASE_URL}/api/financial-data`);
      const rows = await res.json();
      const existingData = await transformSQLData(rows);
      
      // Generate and merge mock data for additional companies
      const mockData = generateMockFinancialData();
      const allData = mergeWithExistingData(existingData, mockData);
      
      // Set state and initialize default comparison companies
      this.setState(
        {
          allCompaniesData: allData,
        },
        () => {
          const allCompanies = Object.keys(allData);
          const defaultCompanies = getDefaultCompanies(allCompanies);
          this.setState(
            {
              defaultComparisonCompanies: defaultCompanies,
              selectedComparisonCompanies: defaultCompanies,
            },
            this.updateComparison
          );
        }
      );
    } catch (err) {
      // Log errors if fetch fails
      console.error("Failed to fetch data:", err);
    }
  }

  // Redraw charts and info buttons when data or section changes
  componentDidUpdate(prevProps, prevState) {
    // If company data changed, redraw all charts
    if (prevState.allCompaniesData !== this.state.allCompaniesData) {
      Object.entries(this.state.allCompaniesData).forEach(([company, data], companyIdx) => {
        // Draw main multi-metric chart (Revenue, Net Profit Margin, Gross Margin)
        const revenueValues = data.metrics["Revenue"];
        const netProfitMarginValues = data.metrics["Net Profit Margin"];
        const grossMarginValues = data.metrics["Gross Margin"];
        let metricsToShow = ["Revenue", "Net Profit Margin", "Gross Margin"];
        let colorsToShow = CHART_COLOR_SCHEMES["revenue-margins"];
        let chartTypes = ["bar", "line", "line"];
        let yAxes = [
          { position: "left", label: "Revenue (USD Millions)", metrics: ["Revenue"] },
          { position: "right", label: "Margin (%)", metrics: ["Net Profit Margin", "Gross Margin"] }
        ];
        // Only draw if all metrics have valid data
        if (
          Array.isArray(revenueValues) && revenueValues.some(v => v !== null && !isNaN(v)) &&
          Array.isArray(netProfitMarginValues) && netProfitMarginValues.some(v => v !== null && !isNaN(v)) &&
          Array.isArray(grossMarginValues) && grossMarginValues.some(v => v !== null && !isNaN(v))
        ) {
          drawMultiMetricChart(
            `chart-revenue-margins-${company}`,
            "bar",
            data,
            metricsToShow,
            colorsToShow,
            {
              title: "REVENUE vs NET PROFIT MARGIN vs GROSS MARGIN",
              yLabel: "Revenue (USD Millions)",
              yLabelSecondary: "Margin (%)",
              yTickFormat: v => v.toFixed(1),
              yTickFormatSecondary: v => v.toFixed(1) + "%",
              tooltipFormat: v => v.toFixed(1),
              axisFontSize: 24,
              chartTypes,
              yAxes,
              legend: [
                { label: "Revenue", color: colorsToShow[0], type: "bar" },
                { label: "Net Profit Margin", color: colorsToShow[1], type: "line" },
                { label: "Gross Margin", color: colorsToShow[2], type: "line" }
              ],
              highlight: true,
              emphasizeTitle: true,
              emphasizeLegend: true
            }
          );
        }

        // Draw ROE and ROA chart if available
        const roeValues = data.metrics["ROE (Return on Equity)"];
        const roaValues = data.metrics["ROA (Return on Assets)"];
        let roeMetrics = [];
        let roeColors = [];
        if (Array.isArray(roeValues) && roeValues.some(v => v !== null && !isNaN(v))) {
          roeMetrics.push("ROE (Return on Equity)");
          roeColors.push(CHART_COLOR_SCHEMES["roe-roa"][0]);
        }
        if (Array.isArray(roaValues) && roaValues.some(v => v !== null && !isNaN(v))) {
          roeMetrics.push("ROA (Return on Assets)");
          roeColors.push(CHART_COLOR_SCHEMES["roe-roa"][1]);
        }
        if (roeMetrics.length > 0) {
          drawMultiMetricChart(
            `chart-roe-roa-${company}`,
            "line",
            data,
            roeMetrics,
            roeColors,
            {
              title: "ROE and ROA",
              yLabel: "In %",
              yTickFormat: v => v.toFixed(1),
              tooltipFormat: v => v.toFixed(1),
              axisFontSize: 20,
              yAxes: [
                { position: "left", label: "In %", metrics: ["ROE (Return on Equity)", "ROA (Return on Assets)"] }
              ]
            }
          );
        }

        // Draw multi-metric percent charts (COGS, SG&A, R&D, Inventories)
        MULTI_METRIC_PERCENT_CHARTS.forEach(chartDef => {
          const { percentMetric, absoluteMetric, label } = chartDef;
          const percentValues = data.metrics[percentMetric];
          const absoluteValues = data.metrics[absoluteMetric];
          
          if (
            Array.isArray(revenueValues) && revenueValues.some(v => v !== null && !isNaN(v)) &&
            Array.isArray(absoluteValues) && absoluteValues.some(v => v !== null && !isNaN(v)) &&
            Array.isArray(percentValues) && percentValues.some(v => v !== null && !isNaN(v))
          ) {
            const chartId = `chart-multi-${percentMetric.replace(/[^a-zA-Z0-9]/g, "")}-${company}`;
            drawMultiMetricChart(
              chartId,
              "bar",
              data,
              ["Revenue", absoluteMetric, percentMetric],
              CHART_COLOR_SCHEMES["multi-metric"],
              {
                title: `Revenue, ${label}, ${percentMetric}`,
                yLabel: "In Millions USD",
                yLabelSecondary: "In %",
                yTickFormat: v => v.toFixed(1),
                yTickFormatSecondary: v => v.toFixed(1) + "%",
                tooltipFormat: v => v.toFixed(1),
                axisFontSize: 20,
                chartTypes: ["bar", "bar", "line"],
                yAxes: [
                  { position: "left", label: "In Millions USD", metrics: ["Revenue", absoluteMetric] },
                  { position: "right", label: "In %", metrics: [percentMetric] }
                ]
              }
            );
          }
        });

        // Draw all other single-metric charts
        METRICS_TO_DISPLAY.forEach((metric, idx) => {
          if (
            ["Revenue", "Net Profit Margin", "Gross Margin", "ROE (Return on Equity)", "ROA (Return on Assets)", "COGS as % of Revenue", "SGM&A as % of Revenue", "R&D as % of Revenue", "Inventories as a % of Revenue"].includes(metric)
          ) {
            return;
          }
          const values = data.metrics[metric];
          if (Array.isArray(values) && values.some(v => v !== null && !isNaN(v))) {
            drawChart(
              `chart-other-${company}-${idx}`,
              "line",
              metric,
              data.quarters,
              values,
              getCompanyColor(companyIdx),
              {
                title: metric,
                yLabel: RATIO_METRICS.includes(metric) ? "" : getMetricUnit(metric),
                xLabel: "Quarter",
                units: RATIO_METRICS.includes(metric) ? "" : getMetricUnit(metric),
                borderWidth: 2,
                borderColor: "#ccc",
                yTickFormat: v => v.toFixed(RATIO_METRICS.includes(metric) ? 2 : 1),
                tooltipFormat: v => v.toFixed(RATIO_METRICS.includes(metric) ? 2 : 1),
                axisFontSize: 20
              }
            );
          }
        });
      });
      
      // Info buttons need to be re-attached after chart redraw
      setTimeout(() => {
        this.addInfoButtonListeners();
      }, 300);
    }
    
    // If user switches section, re-attach info buttons
    if (prevState.activeSection !== this.state.activeSection) {
      setTimeout(() => {
        this.addInfoButtonListeners();
      }, 300);
    }

    // Removed all companyComponents chart logic
  }

  // Attach listeners for info buttons and tooltips
  addInfoButtonListeners() {
    document.querySelectorAll('.info-tooltip').forEach(tooltip => {
      tooltip.style.display = 'none';
    });

    const allInfoButtons = document.querySelectorAll('.info-button');
    allInfoButtons.forEach(button => {
      const newButton = button.cloneNode(true);
      button.parentNode.replaceChild(newButton, button);
    });

    document.querySelectorAll('.info-button').forEach(button => {
      const tooltipId = button.id + '-tooltip';
      const tooltip = document.getElementById(tooltipId);
      
      if (tooltip) {
        button.addEventListener('click', (e) => {
          e.preventDefault();
          e.stopPropagation();
          
          document.querySelectorAll('.info-tooltip').forEach(t => {
            if (t.id !== tooltipId) {
              t.style.display = 'none';
            }
          });
          
          const isVisible = tooltip.style.display === 'block';
          tooltip.style.display = isVisible ? 'none' : 'block';
        });

        button.addEventListener('mouseenter', () => {
          tooltip.style.display = 'block';
        });
        
        button.addEventListener('mouseleave', (e) => {
          setTimeout(() => {
            if (!tooltip.matches(':hover') && !button.matches(':hover')) {
              tooltip.style.display = 'none';
            }
          }, 100);
        });

        tooltip.addEventListener('mouseenter', () => {
          tooltip.style.display = 'block';
        });

        tooltip.addEventListener('mouseleave', () => {
          setTimeout(() => {
            if (!tooltip.matches(':hover') && !button.matches(':hover')) {
              tooltip.style.display = 'none';
            }
          }, 100);
        });
      }
    });

    document.addEventListener('click', (e) => {
      if (!e.target.closest('.info-button-container')) {
        document.querySelectorAll('.info-tooltip').forEach(tooltip => {
          tooltip.style.display = 'none';
        });
      }
    });
  }

  // Handle checkbox for company selection in comparison view
  handleCompanyCheckbox(e, company) {
    let selected = [...this.state.selectedComparisonCompanies];
    if (e.target.checked) {
      if (!selected.includes(company)) selected.push(company);
    } else {
      selected = selected.filter(c => c !== company);
    }
    this.setState({ selectedComparisonCompanies: selected }, this.updateComparison);
  }

  // Update comparison charts when companies are selected
  updateComparison() {
    let selectedCompanies = this.state.selectedComparisonCompanies;

    // Always require at least two companies for comparison
    if (selectedCompanies.length < 2) {
      selectedCompanies = this.state.defaultComparisonCompanies;
      this.setState({ selectedComparisonCompanies: selectedCompanies });
    }

    if (selectedCompanies.length < 2) {
      const comparisonChartsEl = document.getElementById('comparison-charts');
      if (comparisonChartsEl) {
        comparisonChartsEl.innerHTML = '<p style="font-weight: bold; color: #666;">Please select at least two companies to compare.</p>';
      }
      return;
    }

    const firstCompany = this.state.allCompaniesData[selectedCompanies[0]];
    if (!firstCompany) return;
    const metricNames = METRICS_TO_DISPLAY.filter(metric =>
      firstCompany.metrics[metric] &&
      Array.isArray(firstCompany.metrics[metric]) &&
      firstCompany.metrics[metric].some(v => v !== null && !isNaN(v))
    );

    let comparisonHtml = "";

    // Build chart HTML for each metric
    metricNames.forEach((metric, idx) => {
      const hasInfoButton = METRIC_INFO[metric];
      const infoButtonHtml = hasInfoButton ? `
        <div class="info-button-container">
          <button class="info-button" id="info-comparison-${metric.replace(/[^a-zA-Z0-9]/g, "")}-${idx}" type="button" title="Click for more information">ℹ️</button>
          <div class="info-tooltip" id="info-comparison-${metric.replace(/[^a-zA-Z0-9]/g, "")}-${idx}-tooltip" style="display: none;">
            <div class="info-content">
              <h4>${metric}</h4>
              <p><strong>Description:</strong> ${METRIC_INFO[metric]?.description}</p>
              <p><strong>Formula:</strong> ${(METRIC_FORMULAS[metric]?.formula || METRIC_INFO[metric]?.formula)}</p>
            </div>
          </div>
        </div>
      ` : "";
      
      comparisonHtml += `
        <div class="chart-container">
          <h3>${metric}${infoButtonHtml}</h3>
          <canvas id="comparison-${idx}"></canvas>
          <div class="chart-controls">
            <label>Chart Type:</label>
            <select id="selector-${idx}" class="chart-type-selector">
              <option value="line">Line</option>
              <option value="bar">Bar</option>
            </select>
            <button id="toggle-table-${idx}">Show Table</button>
          </div>
        </div>
        <div id="table-container-${idx}" class="comparison-table" style="display:none;"></div>
      `;
    });

    document.getElementById('comparison-charts').innerHTML = comparisonHtml;

    // Attach chart drawing and table toggling logic
    setTimeout(() => {
      metricNames.forEach((metric, idx) => {
        const colors = selectedCompanies.map((c, i) => getCompanyColor(i));
        drawComparisonChart(
          `comparison-${idx}`,
          "line",
          metric,
          selectedCompanies,
          this.state.allCompaniesData,
          colors,
          {
            title: metric,
            yLabel: RATIO_METRICS.includes(metric) ? "" : getMetricUnit(metric),
            xLabel: "Quarter",
            units: RATIO_METRICS.includes(metric) ? "" : getMetricUnit(metric),
            showMarkers: true,
            yTickFormat: v => RATIO_METRICS.includes(metric) ? v.toFixed(2) : Math.round(v),
            tooltipFormat: v => RATIO_METRICS.includes(metric) ? v.toFixed(2) : Math.round(v),
            axisFontSize: 20
          }
        );
        document.getElementById(`selector-${idx}`).addEventListener('change', (e) => {
          drawComparisonChart(
            `comparison-${idx}`,
            e.target.value,
            metric,
            selectedCompanies,
            this.state.allCompaniesData,
            colors,
            {
              title: metric,
              yLabel: RATIO_METRICS.includes(metric) ? "" : getMetricUnit(metric),
              xLabel: "Quarter",
              units: RATIO_METRICS.includes(metric) ? "" : getMetricUnit(metric),
              showMarkers: true,
              yTickFormat: v => RATIO_METRICS.includes(metric) ? v.toFixed(2) : Math.round(v),
              tooltipFormat: v => RATIO_METRICS.includes(metric) ? v.toFixed(2) : Math.round(v),
              axisFontSize: 20
            }
          );
        });
        document.getElementById(`toggle-table-${idx}`).addEventListener('click', () => {
          const container = document.getElementById(`table-container-${idx}`);
          if (container.style.display === "none") {
            let html = `<table><thead><tr><th>Company</th>`;
            const quarters = this.state.allCompaniesData[selectedCompanies[0]].quarters;
            quarters.forEach(q => html += `<th>${q}</th>`);
            html += `</tr></thead><tbody>`;
            selectedCompanies.forEach(c => {
              const companyData = this.state.allCompaniesData[c];
              const values = companyData && companyData.metrics[metric];
              if (Array.isArray(values) && values.some(v => v !== null && !isNaN(v))) {
                html += `<tr><td>${c}</td>`;
                values.forEach(v => html += `<td>${formatTableValue(metric, v, true)}</td>`);
                html += `</tr>`;
              }
            });
            html += `</tbody></table>`;
            container.innerHTML = html;
            container.style.display = "block";
            document.getElementById(`toggle-table-${idx}`).innerText = "Hide Table";
          } else {
            container.style.display = "none";
            document.getElementById(`toggle-table-${idx}`).innerText = "Show Table";
          }
        });
      });
      
      this.addInfoButtonListeners();
    }, 100);
  }

  // Toggle table display for a chart
  toggleTable(tableId, metric, companies, multiMetricRows) {
    const container = document.getElementById(tableId);
    const company = companies[0];
    const quarters = this.state.allCompaniesData[company].quarters;
    const data = this.state.allCompaniesData[company];

    // Helper to display metric names in table
    const displayMetricName = (name) => {
      if (name === "COGS" || name === "Cost of Goods Sold") return "Cost of Goods Sold";
      if (name === "SGM&A" || name === "SG&A (incl. Marketing)") return "SG&A (incl. Marketing)";
      if (name === "R&D" || name === "Research and development (R&D)") return "Research and development (R&D)";
      return name;
    };
    if (container.style.display === "none") {
      let html = "";
      if (multiMetricRows && Array.isArray(multiMetricRows)) {
        html = `<table><thead><tr><th>Metric</th>`;
        quarters.forEach(q => html += `<th>${q}</th>`);
        html += `</tr></thead><tbody>`;
        multiMetricRows.forEach(metricName => {
          html += `<tr><td>${displayMetricName(metricName)}</td>`;
          const values = data.metrics[metricName] || [];
          values.forEach(v => html += `<td>${formatTableValue(metricName, v)}</td>`);
          html += `</tr>`;
        });
        html += `</tbody></table>`;
      } else {
        html = `<table><thead><tr><th>${company}</th>`;
        quarters.forEach(q => html += `<th>${q}</th>`);
        html += `</tr></thead><tbody><tr><td>${displayMetricName(metric)}</td>`;
        const values = data.metrics[metric];
        values.forEach(v => html += `<td>${formatTableValue(metric, v)}</td>`);
        html += `</tr></tbody></table>`;
      }
      container.innerHTML = html;
      container.style.display = "block";
      container.style.margin = "";
      document.getElementById(tableId.replace("table-container", "toggle-table")).innerText = "Hide Table";
    } else {
      container.style.display = "none";
      container.style.margin = "";
      document.getElementById(tableId.replace("table-container", "toggle-table")).innerText = "Show Table";
    }
  }

  // Toggle display of all metrics table for a company
  toggleAllMetricsTable = (company) => {
    this.setState(prev => ({
      showAllMetricsTable: {
        ...prev.showAllMetricsTable,
        [company]: !prev.showAllMetricsTable[company]
      }
    }));
  };

  // Handle tab navigation
  handleTabChange(tabId) {
    this.setState({ activeTab: tabId });
    
    // Re-render charts after tab change with a slight delay
    setTimeout(() => {
      this.renderTabSpecificCharts(tabId);
    }, 100);
  }

  // Render charts specific to each tab
  renderTabSpecificCharts(tabId) {
    const { allCompaniesData, selectedComparisonCompanies } = this.state;
    
    if (tabId === 'financials') {
      FINANCIALS_METRICS.forEach((metric, idx) => {
        const chartId = `chart-financials-${metric}`;
        this.renderComparisonChart(chartId, metric, selectedComparisonCompanies);
      });
    } else if (tabId === 'income_statement') {
      INCOME_STATEMENT_METRICS.forEach((metric, idx) => {
        const chartId = `chart-income-${metric}`;
        this.renderComparisonChart(chartId, metric, selectedComparisonCompanies);
      });
    } else if (tabId === 'charts') {
      KEY_CHARTS_METRICS.forEach((metric, idx) => {
        const chartId = `chart-key-chart-${metric}`;
        this.renderComparisonChart(chartId, metric, selectedComparisonCompanies);
      });
    }
  }

  // Generic method to render comparison charts
  renderComparisonChart(chartId, metric, companies) {
    const { allCompaniesData } = this.state;
    
    // Wait for DOM element to be available
    setTimeout(() => {
      const element = document.getElementById(chartId);
      if (element && companies.length > 0) {
        const colors = companies.map((_, idx) => COMPANY_COLORS[idx % COMPANY_COLORS.length]);
        
        drawComparisonChart(
          chartId,
          "line",
          metric,
          companies,
          allCompaniesData,
          colors,
          {
            title: metric,
            yLabel: RATIO_METRICS.includes(metric) ? "" : getMetricUnit(metric),
            xLabel: "Quarter",
            units: RATIO_METRICS.includes(metric) ? "" : getMetricUnit(metric),
            showMarkers: true,
            yTickFormat: v => RATIO_METRICS.includes(metric) ? v.toFixed(2) : Math.round(v),
            tooltipFormat: v => RATIO_METRICS.includes(metric) ? v.toFixed(2) : Math.round(v),
            axisFontSize: 16,
            responsive: true
          }
        );
      }
    }, 200);
  }

  // Handle company checkbox selection
  handleCompanyCheckbox(event, company) {
    const isChecked = event.target.checked;
    let newSelection = [...this.state.selectedComparisonCompanies];
    
    if (isChecked) {
      if (!newSelection.includes(company)) {
        newSelection.push(company);
      }
    } else {
      newSelection = newSelection.filter(c => c !== company);
    }
    
    this.setState({ selectedComparisonCompanies: newSelection }, () => {
      this.updateComparison();
      // Re-render tab specific charts if we're not on overview
      if (this.state.activeTab !== 'overview') {
        this.renderTabSpecificCharts(this.state.activeTab);
      }
    });
  }

  // Handle navigation between dashboard sections
  handleNavClick(section) {
    this.setState({ activeSection: section }, async () => {
      document.querySelectorAll(".company-section").forEach(s => s.style.display = "none");
      if (section === "comparison") {
        document.getElementById("comparison-section").style.display = "block";
      } else {
        document.getElementById("comparison-section").style.display = "none";
        document.getElementById("section-" + section).style.display = "block";
        await this.loadStockDataForCompany(section);
      }
      setTimeout(() => {
        this.addInfoButtonListeners();
      }, 300);
    });
  }

  // Main render function for dashboard UI
  render() {
    const hasData = Object.keys(this.state.allCompaniesData).length > 0;
    const {
      activeSection,
      selectedComparisonCompanies,
      showAllMetricsTable,
    } = this.state;
    const companyList = Object.keys(this.state.allCompaniesData);

    if (!hasData) {
      return (
        <div className="dashboard-loading">
          <h1>Financial Dashboard</h1>
          <p style={{ color: 'red', fontWeight: 'bold' }}>No data loaded from backend. Please check your backend API and database connection.</p>
          <p>Try visiting your backend API endpoint directly to confirm it returns data:</p>
          <ul>
            <li><a href="https://financial-dashboard-backend-7p3w.onrender.com/api/financial-data" target="_blank" rel="noopener noreferrer">/api/financial-data</a></li>
          </ul>
        </div>
      );
    }

    return (
      <div style={{ paddingLeft: "32px" }}>
        <h1 className="dashboard-header">Financial Dashboard</h1>
        
        {/* Tab Navigation */}
        <TabNavigation 
          activeTab={this.state.activeTab}
          onTabChange={this.handleTabChange}
          tabs={DASHBOARD_TABS}
        />
        
        {/* Show loading message if data not loaded yet */}
        {!hasData && (
          <div className="upload-message">
            Loading data from database...
          </div>
        )}

        {/* Main dashboard content */}
        {hasData && (
          <>
            {/* Tab Content */}
            {this.state.activeTab === 'overview' && (
              <>
                {/* Navigation bar for dashboard sections */}
                <nav className="navbar">
                  <button
                    className={`show-comparison${activeSection === "comparison" ? " active" : ""}`}
                    onClick={() => this.handleNavClick("comparison")}
                  >
                    Comparison
                  </button>
              {companyList.map(company => (
                <button
                  key={company}
                  className={activeSection === company ? "active" : ""}
                  onClick={() => this.handleNavClick(company)}
                >
                  {company}
                </button>
              ))}
            </nav>

            {/* Comparison section for multiple companies */}
            <div
              id="comparison-section"
              className="comparison-section"
              style={{ display: activeSection === "comparison" ? "block" : "none" }}
            >
              <div>
                <h3 style={{ color: "#222" }}>Select Companies to Compare:</h3>
                {companyList.map(company => (
                  <label key={company} style={{ display: "inline-block", margin: "5px 15px 5px 0", fontWeight: "bold" }}>
                    <input
                      type="checkbox"
                      className="company-checkbox"
                      value={company}
                      style={{ marginRight: "5px" }}
                      checked={selectedComparisonCompanies.includes(company)}
                      onChange={e => this.handleCompanyCheckbox(e, company)}
                    />
                    {company}
                  </label>
                ))}
              </div>
              <div id="comparison-charts" className="comparison-charts">
                <p style={{ fontWeight: "bold", color: "#666" }}>Please select companies to compare.</p>
              </div>
            </div>

            {/* Individual company sections */}
            {companyList.map((company, companyIdx) => {
              const data = this.state.allCompaniesData[company];
              const ticker = data.ticker;
              const stockData = this.state.stockData[ticker];
              
              return (
                <div
                  key={company}
                  id={"section-" + company}
                  className="company-section"
                  style={{ display: activeSection === company ? "block" : "none" }}
                >
                  {/* Company header and live ticker info */}
                  <div style={{ marginBottom: "20px" }}>
                    <h2 style={{ color: "#222", marginBottom: "5px" }}>{company}</h2>
                    {ticker && (
                      <div style={{ 
                        display: "flex",
                        alignItems: "center",
                        gap: "10px",
                        flexWrap: "wrap"
                      }}>
                        <div style={{ 
                          display: "inline-block",
                          backgroundColor: "#f0f0f0",
                          padding: "8px 12px",
                          borderRadius: "6px",
                          fontSize: "14px",
                          fontWeight: "bold",
                          color: "#666",
                          border: "1px solid #ddd"
                        }}>
                          Ticker: {ticker}
                        </div>
                        {/* Show live price if available */}
                        {stockData && stockData.price !== undefined ? (
                          <div style={{
                            display: "flex",
                            alignItems: "center",
                            gap: "15px",
                            backgroundColor: "#fff",
                            padding: "8px 15px",
                            borderRadius: "6px",
                            border: "1px solid #ddd",
                            boxShadow: "0 2px 4px rgba(0,0,0,0.1)"
                          }}>
                            <div style={{ fontSize: "16px", fontWeight: "bold", color: "#333" }}>
                              ${stockData.price?.toFixed(2)}
                            </div>
                            {(typeof stockData.change === 'number' && typeof stockData.changePercent === 'number') ? (
                              <div style={{ 
                                fontSize: "14px", 
                                color: parseFloat(stockData.change) >= 0 ? "#4CAF50" : "#f44336",
                                fontWeight: "500"
                              }}>
                                {parseFloat(stockData.change) >= 0 ? "+" : ""}{stockData.change?.toFixed(2)} 
                                ({parseFloat(stockData.changePercent) >= 0 ? "+" : ""}{parseFloat(stockData.changePercent)?.toFixed(2)}%)
                              </div>
                            ) : null}
                            <div style={{ fontSize: "12px", color: "#888" }}>
                              Updated: {stockData.lastUpdated}
                            </div>
                          </div>
                        ) : ticker && stockData === null ? (
                          <div style={{
                            padding: "8px 12px",
                            fontSize: "12px",
                            color: "#f44336",
                            fontStyle: "italic"
                          }}>
                            Live price not available for this ticker
                          </div>
                        ) : null}
                        {/* Loading message for live data */}
                        {ticker && !stockData && (
                          <div style={{
                            padding: "8px 12px",
                            fontSize: "12px",
                            color: "#888",
                            fontStyle: "italic"
                          }}>
                            Loading live data...
                          </div>
                        )}
                      </div>
                    )}
                  </div>
                  {/* Main charts for the company */}
                  <div className="charts-wrapper">
                    {/* Multi-metric chart for revenue and margins */}
                    <div className="chart-container">
                      <h3>Revenue, Net Profit Margin, Gross Margin</h3>
                      <canvas id={`chart-revenue-margins-${company}`}></canvas>
                      <div className="chart-controls">
                        <label>Chart Type:</label>
                        <select
                          id={`selector-revenue-margins-${company}`}
                          className="chart-type-selector"
                          defaultValue="line"
                          onChange={e =>
                            drawMultiMetricChart(
                              `chart-revenue-margins-${company}`,
                              e.target.value,
                              data,
                              ["Revenue", "Net Profit Margin", "Gross Margin"],
                              CHART_COLOR_SCHEMES["revenue-margins"],
                              { 
                                title: "Revenue, Net Profit Margin, Gross Margin",
                                yLabel: "Revenue (USD Millions)",
                                yLabelSecondary: "Margin (%)",
                                yTickFormat: v => v.toFixed(1),
                                yTickFormatSecondary: v => v.toFixed(1) + "%",
                                tooltipFormat: v => v.toFixed(1),
                                axisFontSize: 20,
                                chartTypes: ["bar", "line", "line"],
                                yAxes: [
                                  { position: "left", label: "Revenue (USD Millions)", metrics: ["Revenue"] },
                                  { position: "right", label: "Margin (%)", metrics: ["Net Profit Margin", "Gross Margin"] }
                                ]
                              }
                            )
                          }
                        >
                          <option value="line">Line</option>
                          <option value="bar">Bar</option>
                        </select>
                        <button
                          id={`toggle-table-revenue-margins-${company}`}
                          onClick={() =>
                            this.toggleTable(
                              `table-container-revenue-margins-${company}`,
                              null,
                              [company],
                              ["Revenue", "Net Profit Margin", "Gross Margin"]
                            )
                          }
                        >
                          Show Table
                        </button>
                      </div>
                    </div>
                    <div id={`table-container-revenue-margins-${company}`} className="table-container individual-company-table" style={{ display: "none" }}></div>

                    {/* ROE and ROA chart */}
                    <div className="chart-container">
                      <h3 style={{ display: "flex", alignItems: "center", flexWrap: "wrap", gap: "5px" }}>
                        ROE, ROA
                        <InfoButton metric="ROE (Return on Equity)" info={{
                          ...METRIC_INFO["ROE (Return on Equity)"],
                          formula: METRIC_FORMULAS["ROE (Return on Equity)"].formula
                        }} id={`${company}-roe`} />
                        <InfoButton metric="ROA (Return on Assets)" info={{
                          ...METRIC_INFO["ROA (Return on Assets)"],
                          formula: METRIC_FORMULAS["ROA (Return on Assets)"].formula
                        }} id={`${company}-roa`} />
                      </h3>
                      <canvas id={`chart-roe-roa-${company}`}></canvas>
                      <div className="chart-controls">
                        <label>Chart Type:</label>
                        <select
                          id={`selector-roe-roa-${company}`}
                          className="chart-type-selector"
                          defaultValue="line"
                          onChange={e =>
                            drawMultiMetricChart(
                              `chart-roe-roa-${company}`,
                              e.target.value,
                              data,
                              ["ROE (Return on Equity)", "ROA (Return on Assets)"],
                              CHART_COLOR_SCHEMES["roe-roa"],
                              { 
                                title: "ROE, ROA",
                                yLabel: "In %",
                                yTickFormat: v => v.toFixed(1),
                                tooltipFormat: v => v.toFixed(1),
                                axisFontSize: 20,
                                yAxes: [
                                  { position: "left", label: "In %", metrics: ["ROE (Return on Equity)", "ROA (Return on Assets)"] }
                                ]
                              }
                            )
                          }
                        >
                          <option value="line">Line</option>
                          <option value="bar">Bar</option>
                        </select>
                        <button
                          id={`toggle-table-roe-roa-${company}`}
                          onClick={() =>
                            this.toggleTable(
                              `table-container-roe-roa-${company}`,
                              null,
                              [company],
                              ["ROE (Return on Equity)", "ROA (Return on Assets)"]
                            )
                          }
                        >
                          Show Table
                        </button>
                      </div>
                    </div>
                    <div id={`table-container-roe-roa-${company}`} className="table-container individual-company-table" style={{ display: "none" }}></div>

                    {/* Multi-metric percent charts for COGS, SG&A, R&D, Inventories */}
                    {MULTI_METRIC_PERCENT_CHARTS.map((chartDef, idx) => {
                      const { percentMetric, absoluteMetric, label } = chartDef;
                      const percentValues = data.metrics[percentMetric];
                      const absoluteValues = data.metrics[absoluteMetric];
                      const revenueValues = data.metrics["Revenue"];
                      if (
                        Array.isArray(percentValues) && percentValues.some(v => v !== null && !isNaN(v)) &&
                        Array.isArray(absoluteValues) && absoluteValues.some(v => v !== null && !isNaN(v)) &&
                        Array.isArray(revenueValues) && revenueValues.some(v => v !== null && !isNaN(v))
                      ) {
                        const chartId = `chart-multi-${percentMetric.replace(/[^a-zA-Z0-9]/g, "")}-${company}`;
                        let customTitle = percentMetric;
                        if (percentMetric === "Inventories as a % of Revenue") customTitle = "Inventories as a % of Revenue";
                        else if (percentMetric === "COGS as % of Revenue") customTitle = "COGS as % of Revenue";
                        else if (percentMetric === "SGM&A as % of Revenue") customTitle = "SGM&A as % of Revenue";
                        else if (percentMetric === "R&D as % of Revenue") customTitle = "R&D as % of Revenue";
                        else customTitle = `Revenue, ${label}, ${percentMetric}`;

                        return (
                          <React.Fragment key={`multi-${percentMetric}-${company}`}>
                            <div className="chart-container">
                              <h3>{customTitle}</h3>
                              <canvas id={chartId}></canvas>
                              <div className="chart-controls">
                                <label>Chart Type:</label>
                                <select
                                  id={`selector-multi-${percentMetric.replace(/[^a-zA-Z0-9]/g, "")}-${company}`}
                                  className="chart-type-selector"
                                  defaultValue="bar"
                                  onChange={e =>
                                    drawMultiMetricChart(
                                      chartId,
                                      e.target.value,
                                      data,
                                      ["Revenue", absoluteMetric, percentMetric],
                                      CHART_COLOR_SCHEMES["multi-metric"],
                                      {
                                        title: customTitle,
                                        yLabel: "In Millions USD",
                                        yLabelSecondary: "In %",
                                        yTickFormat: v => v.toFixed(1),
                                        yTickFormatSecondary: v => v.toFixed(1) + "%",
                                        tooltipFormat: v => v.toFixed(1),
                                        axisFontSize: 20,
                                        chartTypes: ["bar", "bar", "line"],
                                        yAxes: [
                                          { position: "left", label: "In Millions USD", metrics: ["Revenue", absoluteMetric] },
                                          { position: "right", label: "In %", metrics: [percentMetric] }
                                        ]
                                      }
                                    )
                                  }
                                >
                                  <option value="bar">Bar/Line</option>
                                  <option value="line">Line</option>
                                </select>
                                <button
                                  id={`toggle-table-multi-${percentMetric.replace(/[^a-zA-Z0-9]/g, "")}-${company}`}
                                  onClick={() =>
                                    this.toggleTable(
                                      `table-container-multi-${percentMetric.replace(/[^a-zA-Z0-9]/g, "")}-${company}`,
                                      null,
                                      [company],
                                      ["Revenue", absoluteMetric, percentMetric]
                                    )
                                  }
                                >
                                  Show Table
                                </button>
                              </div>
                            </div>
                            <div id={`table-container-multi-${percentMetric.replace(/[^a-zA-Z0-9]/g, "")}-${company}`} className="table-container individual-company-table" style={{ display: "none" }}></div>
                          </React.Fragment>
                        );
                      }
                      return null;
                    })}

                    {/* Render all other single-metric charts */}
                    {METRICS_TO_DISPLAY.map((metric, idx) => {
                      if (
                        ["Revenue", "Net Profit Margin", "Gross Margin", "ROE (Return on Equity)", "ROA (Return on Assets)", "COGS as % of Revenue", "SGM&A as % of Revenue", "R&D as % of Revenue", "Inventories as a % of Revenue"].includes(metric)
                      ) return null;
                      const values = data.metrics[metric];
                      if (!Array.isArray(values) || !values.some(v => v !== null && !isNaN(v))) return null;
                      return (
                        <React.Fragment key={`other-${company}-${idx}`}>
                          <div className="chart-container">
                            <h3 style={{ display: "flex", alignItems: "center", flexWrap: "wrap", gap: "5px" }}>
                              {metric}
                              {RATIO_METRICS.includes(metric) && <InfoButton metric={metric} info={{
                                ...METRIC_INFO[metric],
                                formula: METRIC_FORMULAS[metric]?.formula || METRIC_INFO[metric]?.formula
                              }} id={`${company}-${idx}`} />}
                            </h3>
                            <canvas id={`chart-other-${company}-${idx}`}></canvas>
                            <div className="chart-controls">
                              <label>Chart Type:</label>
                              <select
                                id={`selector-other-${company}-${idx}`}
                                className="chart-type-selector"
                                defaultValue="line"
                                onChange={e =>
                                  drawChart(
                                    `chart-other-${company}-${idx}`,
                                    e.target.value,
                                    metric,
                                    data.quarters,
                                    values,
                                    getCompanyColor(companyIdx),
                                    {
                                      title: metric,
                                      yLabel: RATIO_METRICS.includes(metric) ? "" : getMetricUnit(metric),
                                      xLabel: "Quarter",
                                      units: RATIO_METRICS.includes(metric) ? "" : getMetricUnit(metric),
                                      borderWidth: 2,
                                      borderColor: "#ccc",
                                      yTickFormat: v => v.toFixed(RATIO_METRICS.includes(metric) ? 2 : 1),
                                      tooltipFormat: v => v.toFixed(RATIO_METRICS.includes(metric) ? 2 : 1),
                                      axisFontSize: 20
                                    }
                                  )
                                }
                              >
                                <option value="line">Line</option>
                                <option value="bar">Bar</option>
                              </select>
                              <button
                                id={`toggle-table-other-${company}-${idx}`}
                                onClick={() =>
                                  this.toggleTable(
                                    `table-container-other-${company}-${idx}`,
                                    metric,
                                    [company]
                                  )
                                }
                              >
                                Show Table
                              </button>
                            </div>
                          </div>
                          <div id={`table-container-other-${company}-${idx}`} className="table-container individual-company-table" style={{ display: "none" }}></div>
                        </React.Fragment>
                      );
                    })}

                    {/* Button to show/hide all metrics table */}
                    <div style={{ marginTop: 24 }}>
                      <button
                        className="show-all-metrics-btn"
                        onClick={() => this.toggleAllMetricsTable(company)}
                      >
                        {showAllMetricsTable[company] ? "Hide All Metrics Table" : "Show All Metrics Table"}
                      </button>
                    </div>
                    {/* Render all metrics table if toggled */}
                    {showAllMetricsTable[company] && (
                      <div className="table-container all-metrics-table" style={{ marginTop: 12 }}>
                        <table>
                          <thead>
                            <tr>
                              <th>Metric</th>
                              {data.quarters.map(q => (
                                <th key={q}>{q}</th>
                              ))}
                            </tr>
                          </thead>
                          <tbody>
                            {Object.keys(data.metrics).map(metric => {
                              let displayMetric = metric;
                              if (metric === "COGS" || metric === "Cost of Goods Sold") displayMetric = "Cost of Goods Sold";
                              else if (metric === "SGM&A" || metric === "SG&A (incl. Marketing)") displayMetric = "SG&A (incl. Marketing)";
                              else if (metric === "R&D" || metric === "Research and development (R&D)") displayMetric = "Research and development (R&D)";
                              return (
                                <tr key={metric}>
                                  <td>{displayMetric}</td>
                                  {data.metrics[metric].map((v, i) => (
                                    <td key={i}>{formatTableValue(metric, v)}</td>
                                  ))}
                                </tr>
                              );
                            })}
                          </tbody>
                        </table>
                      </div>
                    )}
                  </div>
                </div>
              );
            })}
              </>
            )}

            {/* Financials Tab */}
            {this.state.activeTab === 'financials' && (
              <FinancialsTab 
                allCompaniesData={this.state.allCompaniesData}
                selectedComparisonCompanies={selectedComparisonCompanies}
                showTable={this.state.showTable}
                toggleTable={this.toggleTable}
              />
            )}

            {/* Income Statement Tab */}
            {this.state.activeTab === 'income_statement' && (
              <IncomeStatementTab 
                allCompaniesData={this.state.allCompaniesData}
                selectedComparisonCompanies={selectedComparisonCompanies}
                showTable={this.state.showTable}
                toggleTable={this.toggleTable}
              />
            )}

            {/* Charts Tab */}
            {this.state.activeTab === 'charts' && (
              <ChartsTab 
                allCompaniesData={this.state.allCompaniesData}
                selectedComparisonCompanies={selectedComparisonCompanies}
                showTable={this.state.showTable}
                toggleTable={this.toggleTable}
              />
            )}

            {/* News & Analysis Tab */}
            {this.state.activeTab === 'news_analysis' && (
              <NewsAnalysisTab 
                selectedComparisonCompanies={selectedComparisonCompanies}
              />
            )}

          </>
        )}
      </div>
    );
  }
}

export default Dashboard;
