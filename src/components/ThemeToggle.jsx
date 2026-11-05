import { useState } from "react";
import { Moon, Sun } from "@phosphor-icons/react";
import { Button } from "./ui.jsx";

/*
 * Light/dark toggle, rendered in Header.jsx between search and the XP chip.
 * The initial theme is set before paint by the script in header.php,
 * so reading data-theme here is safe.
 */
export const THEME_KEY = "wod-theme";

const current = () => (document.documentElement.dataset.theme === "light" ? "light" : "dark");

export function ThemeToggle({ className }) {
  const [theme, setTheme] = useState(current);
  const isLight = theme === "light";

  const toggle = () => {
    const next = isLight ? "dark" : "light";
    const html = document.documentElement;
    html.classList.add("theme-transition");
    html.dataset.theme = next;
    window.setTimeout(() => html.classList.remove("theme-transition"), 300);
    // Browser chrome follows the page ground colour of the new theme.
    const bg = getComputedStyle(html).getPropertyValue("--color-bg").trim();
    if (bg) document.querySelector('meta[name="theme-color"]')?.setAttribute("content", bg);
    try {
      localStorage.setItem(THEME_KEY, next);
    } catch {
      /* private mode: theme still switches for this visit */
    }
    setTheme(next);
  };

  return (
    <Button
      variant="secondary"
      onClick={toggle}
      aria-label={isLight ? "Switch to dark theme" : "Switch to light theme"}
      title={isLight ? "Dark mode" : "Light mode"}
      className={`size-[34px] p-0 text-neutral-400 hover:text-accent-300 ${className ?? ""}`}
    >
      {isLight ? <Moon size={16} /> : <Sun size={16} />}
    </Button>
  );
}
