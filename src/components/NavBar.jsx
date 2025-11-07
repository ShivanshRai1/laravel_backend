import { Link } from 'react-router-dom';

function NavBar() {
  return (
    <nav style={{ display: 'flex', gap: 16, padding: 16, background: '#f5f5f5', borderBottom: '1px solid #ddd' }}>
      <Link to="/">Dashboard</Link>
      <Link to="/upload">Upload</Link>
      <Link to="/uploaded-data">Uploaded Data</Link>
      <Link to="/uploaded-charts">Uploaded Charts</Link>
    </nav>
  );
}

export default NavBar;