import { StrictMode } from "react";
import { createRoot } from "react-dom/client";
import "./index.css";
import App from "./App.jsx";
import { GameProvider } from "./lib/game.jsx";

const root = document.getElementById("wod-app");

if (root) {
  createRoot(root).render(
    <StrictMode>
      <GameProvider>
        <App />
      </GameProvider>
    </StrictMode>
  );
}
