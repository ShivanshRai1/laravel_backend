import React from "react";
import * as XLSX from "xlsx";
import jsPDF from "jspdf";
import html2canvas from "html2canvas";

/**
 * TableExportButtons
 * Props:
 *   tableId: string (id of the table DOM element to export)
 *   filename: string (base filename for export)
 *   data: array (optional, for direct export)
 *   columns: array (optional, for direct export)
 */
const TableExportButtons = ({ tableId, filename = "export", data, columns }) => {
  // Export table as CSV/Excel using SheetJS
  const handleExportExcel = (type) => {
    let ws, wb;
    if (data && columns) {
      // Export from data/columns props
      const exportData = [columns, ...data.map(row => columns.map(col => row[col]))];
      ws = XLSX.utils.aoa_to_sheet(exportData);
    } else {
      // Export from DOM table
      const table = document.getElementById(tableId);
      ws = XLSX.utils.table_to_sheet(table);
    }
    wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Sheet1");
    XLSX.writeFile(wb, `${filename}.${type}`);
  };

  // Export table as PDF using html2canvas + jsPDF
  const handleExportPDF = async () => {
    const table = document.getElementById(tableId);
    if (!table) return;
    const canvas = await html2canvas(table);
    const imgData = canvas.toDataURL("image/png");
    const pdf = new jsPDF({ orientation: "landscape" });
    const pageWidth = pdf.internal.pageSize.getWidth();
    const pageHeight = pdf.internal.pageSize.getHeight();
    const imgProps = pdf.getImageProperties(imgData);
    const pdfWidth = pageWidth;
    const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
    pdf.addImage(imgData, "PNG", 0, 10, pdfWidth, pdfHeight);
    pdf.save(`${filename}.pdf`);
  };

  return (
    <div style={{ display: "flex", gap: 8, marginBottom: 8 }}>
      <button onClick={() => handleExportExcel("csv")}>Export CSV</button>
      <button onClick={() => handleExportExcel("xlsx")}>Export Excel</button>
      <button onClick={handleExportPDF}>Export PDF</button>
    </div>
  );
};

export default TableExportButtons;
