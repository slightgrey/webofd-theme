import { useEffect, useState } from "react";
import { Trophy } from "@phosphor-icons/react";
import { useGame } from "../lib/game.jsx";
import { cx } from "../lib/cx.js";

export default function Toast() {
  const { toast } = useGame();
  // Keep the last message on screen while it animates out.
  const [shown, setShown] = useState(null);

  useEffect(() => {
    if (toast) setShown(toast);
  }, [toast]);

  return (
    <div
      role="status"
      aria-live="polite"
      className={cx(
        "pointer-events-none fixed bottom-6 left-6 z-60 transition-[transform,opacity] duration-[320ms,220ms] ease-pop",
        toast ? "translate-y-0 scale-100 opacity-100" : "translate-y-6 scale-[0.96] opacity-0"
      )}
    >
      {shown && (
        <div className="flex items-center gap-3 rounded-lg bg-surface py-3 pr-4 pl-3 shadow-[0_0_0_1px_var(--color-accent-600),0_0_32px_color-mix(in_srgb,var(--color-accent)_30%,transparent),0_16px_40px_rgba(0,0,0,0.6)]">
          <span className="grid size-9 place-items-center rounded-md bg-accent-900 text-accent-300">
            <Trophy size={18} />
          </span>
          <div>
            <div className="text-[14px]">{shown.title}</div>
            <div className="text-[12px] text-accent-300">{shown.sub}</div>
          </div>
        </div>
      )}
    </div>
  );
}
