import React from "react";
import { Routes, Route, Outlet } from "react-router-dom";
import Upload from "./pages/Upload";
import Dashboard from "./pages/Dashboard";
import UploadedData from "./pages/UploadedData";
import UploadedCharts from "./pages/UploadedCharts";
import NavBar from "./components/NavBar";
import ErrorBoundary from "./components/ErrorBoundary";


function Layout() {
  return (
    <>
      <NavBar />
      <div style={{ marginTop: 24 }}>
        <ErrorBoundary>
          <Outlet />
        </ErrorBoundary>
      </div>
    </>
  );
}

function App() {
  return (
    <Routes>
      <Route element={<Layout />}>
        <Route path="/" element={<Dashboard />} />
        <Route path="/upload" element={<Upload />} />
        <Route path="/uploaded-data" element={<UploadedData />} />
        <Route path="/uploaded-charts" element={<UploadedCharts />} />
      </Route>
    </Routes>
  );
}

export default App;