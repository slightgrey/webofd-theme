import { useEffect, useState } from "react";
import { ArrowUp } from "@phosphor-icons/react";
import { Button } from "./ui.jsx";
import { cx } from "../lib/cx.js";

// Bottom-right, opposite the toasts (bottom-left), so the two never overlap.
export default function BackToTop() {
  const [visible, setVisible] = useState(false);

  useEffect(() => {
    const onScroll = () => setVisible(window.scrollY > 300);
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  return (
    <Button
      variant="primary"
      aria-label="Back to top"
      title="Back to top"
      tabIndex={visible ? 0 : -1}
      aria-hidden={!visible}
      onClick={() => window.scrollTo({ top: 0, behavior: "smooth" })}
      className={cx(
        "fixed right-6 bottom-6 z-40 size-10 bg-[color-mix(in_srgb,var(--color-bg)_78%,transparent)] p-0 shadow-[0_0_24px_color-mix(in_srgb,var(--color-accent)_20%,transparent)] backdrop-blur-[12px] transition-[opacity,transform] duration-[220ms,320ms] ease-pop max-sm:right-4 max-sm:bottom-4",
        visible ? "translate-y-0 opacity-100" : "pointer-events-none translate-y-4 opacity-0"
      )}
    >
      <ArrowUp size={18} />
    </Button>
  );
}
