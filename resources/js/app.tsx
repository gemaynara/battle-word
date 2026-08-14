import { createRoot } from 'react-dom/client';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import HomePage from './pages/HomePage';
import ArenaScreen from './pages/ArenaScreen';
import PlayerScreen from './pages/PlayerScreen';

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<HomePage />} />
        <Route path="/arena/:code" element={<ArenaScreen />} />
        <Route path="/play/:code" element={<PlayerScreen />} />
      </Routes>
    </BrowserRouter>
  );
}

const container = document.getElementById('app');
if (container) {
  const root = createRoot(container);
  root.render(<App />);
}
