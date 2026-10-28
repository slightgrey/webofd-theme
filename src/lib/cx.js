import { extendTailwindMerge } from "tailwind-merge";

// Every `text-[Npx]` here sets font-size only, so it must not cancel an earlier `leading-*`
// (tailwind-merge's default treats font-size as also setting line-height).
const twMerge = extendTailwindMerge({
  override: { conflictingClassGroups: { "font-size": [] } },
});

// Join class names; later Tailwind utilities override conflicting earlier ones.
export const cx = (...classes) => twMerge(classes.filter(Boolean).join(" "));
