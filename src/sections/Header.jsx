import { ArrowUpRight, MagnifyingGlass } from "@phosphor-icons/react";
import { config } from "../lib/config.js";
import { useData } from "../lib/data.js";
import { useGame } from "../lib/game.jsx";
import { Button } from "../components/ui.jsx";

const navLink = "text-[14px] text-inherit no-underline hover:text-accent";

// The search shortcut is Cmd+K on Apple devices and Ctrl+K everywhere else.
const isApple = /Mac|iPhone|iPad|iPod/.test(navigator.userAgentData?.platform ?? navigator.platform ?? "");
const shortcut = isApple ? "⌘K" : "Ctrl K";

export default function Header({ onOpenTrophies, onOpenSearch }) {
  const { site } = useData();
  const { ach } = useGame();

  return (
    <header className="sticky top-[var(--wp-admin--admin-bar--height,0px)] z-20 bg-[color-mix(in_srgb,var(--color-bg)_78%,transparent)] backdrop-blur-[12px]">
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
        {site.hireLabel && site.hireUrl && (
          <a href={site.hireUrl} target="_blank" rel="noopener" className={`${navLink} inline-flex items-center gap-[5px] max-sm:hidden`}>
            {site.hireLabel}
            <ArrowUpRight size={11} />
          </a>
        )}
        <Button variant="secondary" onClick={onOpenSearch} className="gap-2 text-neutral-400" aria-label="Search missions" title={`Search missions (${isApple ? "⌘" : "Ctrl+"}K)`}>
          <MagnifyingGlass size={14} />
          <span className="text-[11px] max-sm:hidden">{shortcut}</span>
        </Button>
        <XpChip onClick={onOpenTrophies} />
      </nav>
      <div className="h-px rule-fade" />
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
