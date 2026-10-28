import { cx } from "../lib/cx.js";

/*
 * Nocturne component classes (.btn, .tag, .card, .dialog) rebuilt from Tailwind utilities.
 * Hover/active tints use srgb color-mix to match the design system exactly.
 */

const button = {
  base: "inline-flex items-center justify-center gap-1.5 rounded-md border border-transparent bg-transparent px-[10.08px] py-[5.6px] font-heading text-[14px] leading-[1.2] font-medium text-text no-underline disabled:opacity-45",
  primary:
    "border-accent text-accent hover:bg-[color-mix(in_srgb,var(--color-accent)_12%,transparent)] hover:text-accent active:bg-[color-mix(in_srgb,var(--color-accent)_22%,transparent)] disabled:hover:bg-transparent",
  secondary:
    "border-divider hover:bg-[color-mix(in_srgb,var(--color-text)_7%,transparent)] active:bg-[color-mix(in_srgb,var(--color-text)_14%,transparent)] disabled:hover:bg-transparent",
  ghost:
    "px-[2.8px] text-accent hover:bg-[color-mix(in_srgb,var(--color-accent)_10%,transparent)] hover:text-accent active:bg-[color-mix(in_srgb,var(--color-accent)_18%,transparent)]",
  icon: "size-9 p-0",
};

export function Button({ variant = "secondary", as: Element = "button", className, ...props }) {
  const extra = Element === "button" && !props.type ? { type: "button" } : {};
  return <Element className={cx(button.base, button[variant], className)} {...extra} {...props} />;
}

const tag = {
  base: "inline-flex items-center rounded-[6px] px-2.5 py-[3px] text-[11px] tracking-[0.02em]",
  sm: "px-[7px] py-px text-[10px]",
  accent: "bg-accent-800 text-accent-100",
  neutral: "bg-neutral-800 text-neutral-100",
  outline: "border border-accent text-accent",
};

export function Tag({ variant = "neutral", size, className, ...props }) {
  return <span className={cx(tag.base, size === "sm" && tag.sm, tag[variant], className)} {...props} />;
}

export function Card({ elev = "sm", as: Element = "div", className, ...props }) {
  const shadow = { sm: "shadow-sm", md: "shadow-md", lg: "shadow-lg" }[elev] ?? "";
  return <Element className={cx("flex flex-col gap-[5.6px] rounded-md bg-surface p-[8.4px]", shadow, className)} {...props} />;
}

export function Kicker({ className, ...props }) {
  return <span className={cx("text-[10px] tracking-[0.1em] text-accent uppercase", className)} {...props} />;
}

export function Dialog({ onClose, label, className, backdropClassName, children }) {
  return (
    <div
      className={cx("fixed inset-0 z-50 grid place-items-center bg-[color-mix(in_srgb,var(--color-neutral-900)_50%,transparent)] p-[11.2px] backdrop-blur-[4px]", backdropClassName)}
      // Close only on clicks on the backdrop itself. Inner clicks must keep bubbling so
      // SpaLinks (listening on document) still sees links inside the dialog.
      onClick={(e) => e.target === e.currentTarget && onClose()}
    >
      <div
        role="dialog"
        aria-modal="true"
        aria-label={label}
        className={cx("flex w-[min(440px,100%)] flex-col gap-[8.4px] rounded-lg bg-surface p-[11.2px] shadow-lg", className)}
      >
        {children}
      </div>
    </div>
  );
}
