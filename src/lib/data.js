import { config } from "./config.js";

const raw = window.wodData ?? {};

const categories = raw.categories ?? [];
const categoryById = new Map(categories.map((c) => [c.id, c]));

// Adds the derived game fields the UI needs everywhere.
export function enrich(p) {
  const cats = (p.categories ?? []).map((id) => categoryById.get(id)).filter(Boolean);
  return {
    ...p,
    cats,
    topic: cats[0]?.name ?? "Uncategorized",
    xp: p.readMinutes * config.xpPerMinute,
    diff: difficulty(p.readMinutes),
  };
}

const posts = (raw.posts ?? []).map(enrich);

export const appData = {
  // The view this page was loaded with. After that, the current view comes from useRoute().
  view: raw.view ?? "home",
  categoryId: raw.categoryId ?? null,
  search: raw.search ?? "",
  // The full post for a single view, with contentHtml, toc and comments.
  post: raw.post ? enrich(raw.post) : null,
  site: raw.site ?? {},
  rest: raw.rest ?? {},
  categories,
  posts,
};

export function useData() {
  return appData;
}

export function difficulty(minutes) {
  return minutes <= 6 ? "Easy" : minutes <= 8 ? "Medium" : "Hard";
}

export function xpLabel(post, done) {
  return done.includes(post.id) ? "Cleared" : "+" + post.xp + " XP";
}

export function initials(name) {
  return name
    .split(/\s+/)
    .filter(Boolean)
    .map((w) => w[0])
    .join("")
    .slice(0, 2)
    .toUpperCase();
}

// Matches the PHP side's 'M j, Y' (e.g. "Oct 28, 2026").
export function formatDate(date = new Date()) {
  return date.toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" });
}
