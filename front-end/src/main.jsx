// StrictMode = extra checks while developing
import { StrictMode } from 'react'
// createRoot = starts React inside the page
import { createRoot } from 'react-dom/client'
// Global styles for the whole site
import './index.css'
// The main App component
import App from './App.jsx'

// Find <div id="root"> in index.html and draw the app inside it
createRoot(document.getElementById('root')).render(
  <StrictMode>
    <App />
  </StrictMode>,
)
