import { useEffect, useRef, useState } from "react";
import { useLocation } from "react-router";
import { ArrowUpRight, List, MagnifyingGlass, X } from "@phosphor-icons/react";
import { config } from "../lib/config.js";
import { useData } from "../lib/data.js";
import { useGame } from "../lib/game.jsx";
import { Button } from "../components/ui.jsx";

const navLink = "text-[14px] text-inherit no-underline hover:text-accent";
// Rows in the mobile menu panel.
const menuLink =
  "flex w-full items-center justify-between gap-3 border-0 bg-transparent px-1 py-3 text-left font-[inherit] text-[15px] leading-[inherit] text-text no-underline row-rule hover:text-accent";

// The search shortcut is Cmd+K on Apple devices and Ctrl+K everywhere else.
const isApple = /Mac|iPhone|iPad|iPod/.test(navigator.userAgentData?.platform ?? navigator.platform ?? "");
const shortcut = isApple ? "⌘K" : "Ctrl K";

export default function Header({ onOpenTrophies, onOpenSearch }) {
  const { site } = useData();
  const { ach } = useGame();
  const location = useLocation();
  const [menuOpen, setMenuOpen] = useState(false);
  const header = useRef(null);
  const hire = site.hireLabel && site.hireUrl;

  // Close the mobile menu on navigation, Escape, or a tap outside the header.
  useEffect(() => setMenuOpen(false), [location.pathname, location.search]);
  useEffect(() => {
    if (!menuOpen) return;
    const onKey = (e) => e.key === "Escape" && setMenuOpen(false);
    const onPointer = (e) => header.current && !header.current.contains(e.target) && setMenuOpen(false);
    window.addEventListener("keydown", onKey);
    document.addEventListener("pointerdown", onPointer);
    return () => {
      window.removeEventListener("keydown", onKey);
      document.removeEventListener("pointerdown", onPointer);
    };
  }, [menuOpen]);

  const openTrophies = () => {
    setMenuOpen(false);
    onOpenTrophies();
  };

  return (
    <header ref={header} className="sticky top-[var(--wp-admin--admin-bar--height,0px)] z-20 bg-[color-mix(in_srgb,var(--color-bg)_78%,transparent)] backdrop-blur-[12px]">
      <nav className="mx-auto flex max-w-[1320px] items-center gap-5 px-6 py-3 text-text max-sm:gap-3 max-sm:px-4">
        <a href={site.homeUrl} className="mr-auto flex items-center gap-2.5 font-heading text-[14px] font-medium text-text no-underline hover:text-accent">
          <span className="grid size-7 place-items-center rounded-md text-[12px] text-accent shadow-[inset_0_0_0_1px_var(--color-accent)]">{site.mark}</span>
          <span>{site.name}</span>
        </a>
        <a href={site.homeUrl} className={`${navLink} max-md:hidden`}>
          Missions
        </a>
        <button type="button" onClick={onOpenTrophies} className={`${navLink} max-md:hidden border-0 bg-transparent p-0 font-[inherit] leading-[inherit]`}>
          Trophies <span className="text-neutral-500">{ach.length}</span>
        </button>
        {(site.menu ?? []).map((item) => (
          <a key={item.id} href={item.url} target={item.target || undefined} className={`${navLink} max-md:hidden`}>
            {item.title}
          </a>
        ))}
        {hire && (
          <a href={site.hireUrl} target="_blank" rel="noopener" className={`${navLink} inline-flex items-center gap-[5px] max-md:hidden`}>
            {site.hireLabel}
            <ArrowUpRight size={11} />
          </a>
        )}
        <Button variant="secondary" onClick={onOpenSearch} className="gap-2 text-neutral-400" aria-label="Search missions" title={`Search missions (${isApple ? "⌘" : "Ctrl+"}K)`}>
          <MagnifyingGlass size={14} />
          <span className="text-[11px] max-sm:hidden">{shortcut}</span>
        </Button>
        <XpChip onClick={onOpenTrophies} />
        <Button
          variant="secondary"
          onClick={() => setMenuOpen(!menuOpen)}
          aria-expanded={menuOpen}
          aria-controls="wod-mobile-menu"
          aria-label={menuOpen ? "Close menu" : "Open menu"}
          className="size-[34px] p-0 text-neutral-300 md:hidden"
        >
          {menuOpen ? <X size={16} /> : <List size={16} />}
        </Button>
      </nav>
      <div className="h-px rule-fade" />

      {menuOpen && (
        <nav
          id="wod-mobile-menu"
          aria-label="Menu"
          className="absolute inset-x-0 top-full bg-bg px-4 pt-1 pb-4 shadow-[0_16px_40px_rgba(0,0,0,0.5)] md:hidden"
        >
          <a href={site.homeUrl} className={menuLink}>
            Missions
          </a>
          <button type="button" onClick={openTrophies} className={menuLink}>
            Trophies <span className="text-[13px] text-neutral-500">{ach.length} unlocked</span>
          </button>
          {(site.menu ?? []).map((item) => (
            <a key={item.id} href={item.url} target={item.target || undefined} className={menuLink}>
              {item.title}
            </a>
          ))}
          {hire && (
            <a href={site.hireUrl} target="_blank" rel="noopener" className={menuLink}>
              {site.hireLabel}
              <ArrowUpRight size={14} className="text-neutral-500" />
            </a>
          )}
        </nav>
      )}
    </header>
  );
}

function XpChip({ onClick }) {
  const { level, rank, xpInLevel } = useGame();
  const pct = (xpInLevel / config.xpPerLevel) * 100;

  return (
    <button
      type="button"
      onClick={onClick}
      aria-label={`Level ${level}, ${rank}. Open trophy case`}
      className="flex items-center gap-2.5 rounded-md border-0 bg-transparent py-[5px] pr-3 pl-[5px] font-[inherit] text-text shadow-sm hover:shadow-[0_0_0_1px_var(--color-accent-700)]"
    >
      <span className="grid size-[30px] place-items-center rounded-sm bg-accent-900 text-[13px] text-accent-200 shadow-[inset_0_0_0_1px_var(--color-accent-700)]">{level}</span>
      <span className="flex w-[110px] flex-col gap-1 max-sm:hidden">
        <span className="flex justify-between text-[10px] leading-[1.55] text-neutral-400">
          <span>{rank}</span>
          <span>
            {xpInLevel}/{config.xpPerLevel}
          </span>
        </span>
        <span className="block h-1 overflow-hidden rounded-[2px] bg-neutral-800">
          <span className="block h-1 bg-accent shadow-[0_0_8px_var(--color-accent)] transition-[width] duration-600 ease-fill" style={{ width: pct + "%" }} />
        </span>
      </span>
    </button>
  );
}
