import { extendTailwindMerge } from "tailwind-merge";

// Every `text-[Npx]` here sets font-size only, so it must not cancel an earlier `leading-*`
// (tailwind-merge's default treats font-size as also setting line-height).
// The light-theme radius/shadow tokens (index.css) are registered so they merge like
// built-in values: `rounded-card` replaces `rounded-md`, `shadow-raised` replaces `shadow-sm`.
const twMerge = extendTailwindMerge({
  override: { conflictingClassGroups: { "font-size": [] } },
  extend: {
    theme: {
      radius: ["card"],
      shadow: ["raised", "raised-lg", "inset", "inset-sm", "hover", "header"],
    },
  },
});

// Join class names; later Tailwind utilities override conflicting earlier ones.
export const cx = (...classes) => twMerge(classes.filter(Boolean).join(" "));
