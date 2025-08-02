import React from "react";
import ReactDOM from "react-dom";
import AppRoute from "./route/AppRoute.js";

function App() {
    return (
        <div className="h-screen bg-gray-100">
            <AppRoute />
        </div>
    );
}

export default App;

if (document.getElementById("app")) {
    ReactDOM.render(<App />, document.getElementById("app"));
}
