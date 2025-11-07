import Chart from "chart.js/auto";

// Store chart instances globally (or use a better scoped solution)
const chartInstances = {};

// Helper to get units from metric name
function getUnits(metric) {
  const match = metric.match(/\(([^)]+)\)/);
  return match ? match[1] : "";
}

// Colors for comparison charts (unique per company)
export const COMPARISON_COMPANY_COLORS = [
  "#5da5a4",
  "#607d8b",
  "#a15c63",
  "#8c5b66",
  "#5c6f91",
  "#4a5c6a",
];

// Single company chart color for metrics
const SINGLE_COMPANY_METRIC_COLOR = "#607d8b";

// Chart background color
const CHART_BG_COLOR = "#FAFAFA";

// Draw a individual company chart
export function drawChart(
  chartId,
  chartType = "line",
  metric,
  quarters,
  values,
  color = SINGLE_COMPANY_METRIC_COLOR,
  options = {}
) {
  // Destroy previous chart if exists
  if (chartInstances[chartId]) {
    chartInstances[chartId].destroy();
  }
  const ctx = document.getElementById(chartId);
  if (!ctx) return;

  const units = options.units || getUnits(metric);
  const title = options.title || metric;
  const yLabel = options.yLabel || metric + (units ? ` (${units})` : "");
  const xLabel = options.xLabel || "Quarter";
  const borderColor = options.borderColor || "#ccc";
  const axisFontSize = options.axisFontSize || 16;

  chartInstances[chartId] = new Chart(ctx, {
    type: chartType,
    data: {
      labels: quarters,
      datasets: [
        {
          label: yLabel,
          data: values,
          borderColor: color,
          backgroundColor: color + "33",
          fill: false,
          tension: 0.2,
          pointRadius: 5,
          pointHoverRadius: 7,
          borderWidth: 3,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false, 
      animation: {
        duration: 800,
        easing: "easeInOutQuad",
      },
      plugins: {
        legend: { display: true, position: "top" },
        title: {
          display: true,
          text: title,
          font: { size: 22, weight: "bold" },
        },
        tooltip: {
          enabled: true,
          mode: "nearest",
          intersect: true,
          callbacks: {
            label: function (context) {
              let label = context.dataset.label || "";
              if (label) label += ": ";
              if (context.parsed.y !== null) {
                return label + (options.tooltipFormat ? options.tooltipFormat(context.parsed.y) : context.parsed.y);
              }
              return label;
            },
          },
        },
        background: {
          color: CHART_BG_COLOR,
        },
      },
      layout: {
        padding: {
          left: 90,
          right: 20,
          top: 20,
          bottom: 20,
        },
      },
      scales: {
        x: {
          title: {
            display: true,
            text: xLabel,
            font: { size: axisFontSize, weight: "bold" },
            color: "#222",
          },
          grid: {
            color: borderColor,
          },
          ticks: {
            maxRotation: 45,
            minRotation: 30,
            autoSkip: false,
            font: { weight: "bold" },
            color: "#222",
          },
        },
        y: {
          title: {
            display: true,
            text: yLabel,
            font: { size: axisFontSize, weight: "bold" },
            color: "#222",
          },
          grid: {
            color: borderColor,
          },
          beginAtZero: false,
          suggestedMin: undefined,
          suggestedMax: undefined,
          ticks: {
            font: { weight: "bold" },
            color: "#222",
            callback: function (value) {
              return value.toFixed(1);
            },
          },
        },
      },
    },
  });
}

// Drawing comparison chart for multiple companies
export function drawComparisonChart(
  chartId,
  chartType = "line",
  metric,
  selectedCompanies,
  allCompaniesData,
  colors = COMPARISON_COMPANY_COLORS,
  options = {}
) {
  // Destroy previous chart if exists
  if (chartInstances[chartId]) {
    chartInstances[chartId].destroy();
  }
  const ctx = document.getElementById(chartId);
  if (!ctx) return;

  const xLabel = options.xLabel || "Quarter";
  const yLabel = options.yLabel || metric;
  const borderColor = options.borderColor || "#ccc";
  const axisFontSize = options.axisFontSize || 16;

  const quarters = allCompaniesData[selectedCompanies[0]].quarters;
  const datasets = selectedCompanies.map((company, i) => ({
    label: company,
    data: allCompaniesData[company].metrics[metric],
    borderColor: colors[i],
    backgroundColor: colors[i] + "33",
    fill: false,
    tension: 0.2,
    pointRadius: 5,
    pointHoverRadius: 7,
    borderWidth: 3,
  }));

  chartInstances[chartId] = new Chart(ctx, {
    type: chartType,
    data: {
      labels: quarters,
      datasets,
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: {
        duration: 800,
        easing: "easeInOutQuad",
      },
      plugins: {
        legend: { display: true, position: "top" },
        title: {
          display: true,
          text: metric,
          font: { size: 22, weight: "bold" },
        },
        tooltip: {
          enabled: true,
          mode: "nearest",
          intersect: true,
          callbacks: {
            label: function (context) {
              let label = context.dataset.label || "";
              if (label) label += ": ";
              if (context.parsed.y !== null) {
                return label + (options.tooltipFormat ? options.tooltipFormat(context.parsed.y) : context.parsed.y);
              }
              return label;
            },
          },
        },
        background: {
          color: CHART_BG_COLOR,
        },
      },
      layout: {
        padding: {
          left: 60,
          right: 20,
          top: 20,
          bottom: 20,
        },
      },
      scales: {
        x: {
          title: {
            display: true,
            text: xLabel,
            font: { size: axisFontSize, weight: "bold" },
            color: "#222",
          },
          grid: {
            color: borderColor,
          },
          ticks: {
            maxRotation: 45,
            minRotation: 30,
            autoSkip: false,
            font: { weight: "bold" },
            color: "#222",
          },
        },
        y: {
          title: {
            display: true,
            text: yLabel,
            font: { size: axisFontSize, weight: "bold" },
            color: "#222",
          },
          grid: {
            color: borderColor,
          },
          beginAtZero: false,
          suggestedMin: undefined,
          suggestedMax: undefined,
          ticks: {
            font: { weight: "bold" },
            color: "#222",
            callback: function (value) {
              return value.toFixed(1);
            },
          },
        },
      },
    },
  });
}

// Drawing multi-metric chart (mixed bar/line, dual y-axes)
export function drawMultiMetricChart(
  chartId,
  chartType,
  companyData,
  metrics, // array of metric names, e.g. ["Revenue", "Inventories", "Inventories as a % of Revenue"]
  colors,
  options = {}
) {
  if (chartInstances[chartId]) {
    chartInstances[chartId].destroy();
  }
  const ctx = document.getElementById(chartId);
  if (!ctx) return;

  const quarters = companyData.quarters;
  const axisFontSize = options.axisFontSize || 16;
  const chartTypes = options.chartTypes || [];

  // For mixed charts, always use "bar" as the type, and set dataset.type
  let datasets = metrics.map((metric, i) => {
    const values = companyData.metrics[metric];
    if (!Array.isArray(values)) return null;
    const type = chartTypes[i] || (chartType === "line" ? "line" : "bar");
    // Assign yAxisID: percent metrics on right, others on left
    let yAxisID = "y";
    if (type === "line" && options.yAxes && options.yAxes[1] && options.yAxes[1].metrics.includes(metric)) {
      yAxisID = "y1";
    } else if (type === "line" && metric.toLowerCase().includes("%")) {
      yAxisID = "y1";
    }
    // Helper to convert hex to rgba with alpha, with fallback
    function hexToRgba(hex, alpha) {
      if (!hex || typeof hex !== 'string') {
        // fallback to gray
        return `rgba(180,180,180,${alpha})`;
      }
      let c = hex.replace('#', '');
      if (c.length === 3) {
        c = c[0]+c[0]+c[1]+c[1]+c[2]+c[2];
      }
      const num = parseInt(c, 16);
      return `rgba(${(num >> 16) & 255},${(num >> 8) & 255},${num & 255},${alpha})`;
    }
    const color = colors[i] || '#b4b4b4';
    return {
      label: metric,
      data: values,
      type: type,
      yAxisID: yAxisID,
      backgroundColor: type === "bar" ? hexToRgba(color, 0.6) : undefined,
      borderColor: color,
      fill: false,
      tension: 0.2,
      pointRadius: 5,
      pointHoverRadius: 7,
      borderWidth: 3,
      order: type === "line" ? 2 : 1
    };
  }).filter(Boolean);

  chartInstances[chartId] = new Chart(ctx, {
    type: "bar", // always "bar" for mixed charts
    data: {
      labels: quarters,
      datasets: datasets
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: true, position: "top" },
        title: {
          display: true,
          text: options.title || metrics.join(", "),
          font: { size: 22, weight: "bold" },
        },
        tooltip: {
          enabled: true,
          mode: "point",
          intersect: true,
          callbacks: {
            label: function (context) {
              let label = context.dataset.label || "";
              if (label) label += ": ";
              if (context.parsed.y !== null) {
                return label + (options.tooltipFormat ? options.tooltipFormat(context.parsed.y) : context.parsed.y);
              }
              return label;
            },
          },
        },
      },
      scales: {
        x: {
          title: {
            display: true,
            text: options.xLabel || "Quarter",
            font: { size: axisFontSize, weight: "bold" },
            color: "#222",
          },
          grid: { color: "#ccc" },
          ticks: { font: { weight: "bold" }, color: "#222" },
        },
        y: {
          type: "linear",
          display: true,
          position: "left",
          title: {
            display: true,
            text: options.yLabel || "In Millions USD",
            font: { size: axisFontSize, weight: "bold" },
            color: "#222",
          },
          grid: { color: "#ccc" },
          ticks: {
            font: { weight: "bold" },
            color: "#222",
            callback: options.yTickFormat || (v => v)
          },
        },
        y1: {
          type: "linear",
          display: true,
          position: "right",
          title: {
            display: true,
            text: options.yLabelSecondary || "In %",
            font: { size: axisFontSize, weight: "bold" },
            color: "#222",
          },
          grid: { drawOnChartArea: false },
          ticks: {
            font: { weight: "bold" },
            color: "#222",
            callback: options.yTickFormatSecondary || (v => v)
          },
        },
      },
    },
  });
}

// Stacked bar chart
export function drawStackedBarChart(canvasId, labels, datasets, options = {}) {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;

  // Destroy previous chart instance if exists
  if (chartInstances[canvasId]) {
    chartInstances[canvasId].destroy();
  }

  // Use imported Chart, not window.Chart
  chartInstances[canvasId] = new Chart(canvas, {
    type: "bar",
    data: {
      labels,
      datasets,
    },
    options: {
      plugins: {
        legend: { display: true },
        title: {
          display: !!options.title,
          text: options.title || "",
        },
      },
      responsive: options.responsive ?? true,
      maintainAspectRatio: options.maintainAspectRatio ?? false,
      scales: {
        x: { stacked: true },
        y: { stacked: true, title: { display: !!options.yLabel, text: options.yLabel || "" } },
      },
    },
  });
}