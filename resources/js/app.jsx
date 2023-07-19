import React from "react";
import { createRoot } from "react-dom/client"
import App from "./pages/App";
import './bootstrap';
import '../css/app.scss';

const root = createRoot(document.getElementById('app'));
root.render(<App />);
