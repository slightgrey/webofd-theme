import { Lock, Trophy } from "@phosphor-icons/react";
import { TROPHIES } from "../lib/config.js";
import { useGame } from "../lib/game.jsx";
import { Button, Dialog } from "../components/ui.jsx";
import { cx } from "../lib/cx.js";

export default function TrophyModal({ onClose }) {
  const { ach, reset } = useGame();

  return (
    <Dialog onClose={onClose} label="Trophy case" className="w-[min(600px,100%)] gap-4">
      <div className="flex items-baseline justify-between">
        <div className="font-heading text-[20px] font-medium">Trophy case</div>
        <span className="text-[12px] text-neutral-500">
          {ach.length} of {TROPHIES.length} unlocked
        </span>
      </div>
      <div className="grid grid-cols-[repeat(auto-fill,minmax(160px,1fr))] gap-2.5">
        {TROPHIES.map((t) => {
          const on = ach.includes(t.id);
          return (
            <div
              key={t.id}
              className={cx(
                "flex flex-col gap-2 rounded-md bg-bg p-3.5",
                on ? "shadow-[0_0_0_1px_var(--color-accent-700),0_0_20px_color-mix(in_srgb,var(--color-accent)_14%,transparent)]" : "opacity-55 shadow-sm"
              )}
            >
              <span className={on ? "text-accent" : "text-neutral-600"}>{on ? <Trophy size={22} aria-label="Unlocked" /> : <Lock size={22} aria-label="Locked" />}</span>
              <div className="text-[14px]">{t.name}</div>
              <div className="text-[12px] text-neutral-500">{t.desc}</div>
            </div>
          );
        })}
      </div>
      <div className="mt-[5.6px] flex justify-between gap-[5.6px]">
        <Button
          variant="ghost"
          className="text-neutral-500"
          onClick={() => {
            reset();
            onClose();
          }}
        >
          Reset progress
        </Button>
        <Button variant="secondary" onClick={onClose}>
          Close
        </Button>
      </div>
    </Dialog>
  );
}
