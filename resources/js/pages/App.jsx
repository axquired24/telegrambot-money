import React from "react";
import {
  createBrowserRouter,
  RouterProvider,
  BrowserRouter,
  Routes,
  Route
} from "react-router-dom";
import Dashboard from "./Dashboard";
import InvalidMsg from "./InvalidMsg";

const App = () => {
    return (
        <BrowserRouter basename="/v2">
            <Routes>
                <Route element={<Dashboard />} path="/" />
                <Route element={<Dashboard />} path="/dashboard" />
                <Route element={<InvalidMsg />} path="/invalid-msg" />
            </Routes>
        </BrowserRouter>
        // <React.StrictMode>
        // </React.StrictMode>
    );
}

export default App;
