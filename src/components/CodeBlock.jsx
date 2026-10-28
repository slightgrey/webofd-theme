import { useEffect, useState } from "react";
import { Button } from "./ui.jsx";

export default function CodeBlock({ lang, code }) {
  const [copied, setCopied] = useState(false);

  useEffect(() => {
    if (!copied) return;
    const timer = setTimeout(() => setCopied(false), 1400);
    return () => clearTimeout(timer);
  }, [copied]);

  const copy = () => {
    navigator.clipboard?.writeText(code).then(
      () => setCopied(true),
      () => {}
    );
  };

  return (
    <div className="mt-2 mb-4 overflow-hidden rounded-md bg-neutral-900 shadow-sm">
      <div className="flex items-center justify-between px-3.5 py-2 text-[11px] tracking-[0.06em] text-neutral-500 uppercase">
        <span>{lang}</span>
        <Button variant="ghost" onClick={copy} className="px-2 py-0.5 text-[12px] tracking-normal normal-case">
          {copied ? "Copied" : "Copy"}
        </Button>
      </div>
      <pre className="m-0 overflow-x-auto px-4 pt-1 pb-4 font-mono text-[13px] leading-[1.65] text-accent-200">
        <code>{code}</code>
      </pre>
    </div>
  );
}
