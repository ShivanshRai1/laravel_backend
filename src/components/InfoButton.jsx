import React from "react";

// InfoButton component displays an info icon and tooltip for a metric
export default function InfoButton({ metric, info, id }) {
  // If no info is provided, don't render anything
  if (!info) return null;

  // Create a unique ID for the info button and tooltip
  const infoId = `info-${metric.replace(/[^a-zA-Z0-9]/g, "")}-${id}`;

  return (
    <div key={infoId} className="info-button-container">
      {/* Info icon button that triggers the tooltip */}
      <button
        className="info-button"
        id={infoId}
        type="button"
        title="Click for more information"
      >
        ℹ️
      </button>
      {/* Tooltip with metric description and formula */}
      <div
        className="info-tooltip"
        id={`${infoId}-tooltip`}
        style={{ display: "none" }}
      >
        <div className="info-content">
          <h4>{metric}</h4>
          <p>
            <strong>Description:</strong> {info.description}
          </p>
          <p>
            <strong>Formula:</strong> {info.formula}
          </p>
        </div>
      </div>
    </div>
  );
}