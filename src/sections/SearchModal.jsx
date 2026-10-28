import { useState } from "react";
import { MagnifyingGlass } from "@phosphor-icons/react";
import { useData, xpLabel } from "../lib/data.js";
import { useGame } from "../lib/game.jsx";
import { Dialog } from "../components/ui.jsx";
import { cx } from "../lib/cx.js";

export default function SearchModal({ initialQuery, onClose }) {
  const { posts } = useData();
  const { done } = useGame();
  const [query, setQuery] = useState(initialQuery);

  // Live filter over titles, excerpts and category names.
  const q = query.trim().toLowerCase();
  const results = posts.filter((p) => !q || [p.title, p.excerpt, ...p.cats.map((c) => c.name)].join(" ").toLowerCase().includes(q));

  return (
    <Dialog onClose={onClose} label="Search missions" className="w-[min(560px,100%)] gap-0 overflow-hidden p-0" backdropClassName="items-start justify-items-center pt-[14vh]">
      <div className="flex items-center gap-2.5 px-4 py-3.5">
        <MagnifyingGlass size={16} className="text-neutral-500" />
        <input
          autoFocus
          value={query}
          onChange={(e) => setQuery(e.target.value)}
          placeholder="Search missions…"
          aria-label="Search missions"
          className="flex-1 border-0 bg-transparent font-[inherit] text-[16px] text-text outline-none placeholder:text-neutral-500 focus-visible:outline-none"
        />
        <span className="text-[11px] text-neutral-500">Esc</span>
      </div>
      <div className="h-px bg-divider" />
      <div className="max-h-[360px] overflow-y-auto p-1.5">
        {results.map((p) => (
          <a
            key={p.id}
            href={p.url}
            className="flex justify-between gap-3 rounded-md px-3 py-2.5 text-text no-underline hover:bg-[color-mix(in_srgb,var(--color-accent)_12%,transparent)] hover:text-text focus-visible:bg-[color-mix(in_srgb,var(--color-accent)_12%,transparent)]"
          >
            <div>
              <div className="text-[14px]">{p.title}</div>
              <div className="text-[11px] text-neutral-500">
                {p.topic} · {p.diff}
              </div>
            </div>
            <span className={cx("text-[12px] whitespace-nowrap", done.includes(p.id) ? "text-neutral-600" : "text-accent-300")}>{xpLabel(p, done)}</span>
          </a>
        ))}
        {results.length === 0 && <div className="px-3 py-5 text-[13px] text-neutral-500">No missions match “{query}”.</div>}
      </div>
    </Dialog>
  );
}
