/**
 * ===========================================
 * Point d'entrée de l'application React
 * ===========================================
 * 
 * Initialise React et le routeur.
 * Importe les styles globaux et GrapesJS.
 */

import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';
import App from './App';

// Styles globaux
import './styles/index.css';
import './styles/builder.css';

// Styles GrapesJS (requis)
import 'grapesjs/dist/css/grapes.min.css';

ReactDOM.createRoot(document.getElementById('root')).render(
  <React.StrictMode>
    <BrowserRouter basename="/builder">
      <App />
    </BrowserRouter>
  </React.StrictMode>
);
