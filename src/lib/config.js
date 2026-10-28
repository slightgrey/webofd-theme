// Game tuning. Defaults here; Appearance → Web Of D overrides them via window.wodData.config.
export const config = {
  autoComplete: true, // clear a mission when scroll passes 92%
  showQuiz: true,
  showToc: true,
  xpPerLevel: 150,
  skillsShown: 5,

  xpPerMinute: 15,
  quizXp: 40,
  commentXp: 10,
  autoCompleteAt: 0.92,
  shopCategorySlug: "woocommerce", // the "shop" quest and trophy; see shopTrack() in progress.js
  spa: true, // switch views without a full page load
  ...window.wodData?.config,
};

export const STORAGE_KEY = "wod-game-v1";

export const RANKS = ["Hello World", "Hook Apprentice", "Plugin Smith", "Block Architect", "Store Engineer", "Headless Sage"];

export const TROPHIES = [
  { id: "first", name: "First commit", desc: "Complete your first mission" },
  { id: "quiz1", name: "Sharp eye", desc: "Ace a checkpoint quiz" },
  { id: "quiz3", name: "Code reviewer", desc: "Ace 3 checkpoint quizzes" },
  { id: "explorer", name: "Full stack", desc: "Complete missions in 3 topics" },
  { id: "woo", name: "Shopkeeper", desc: "Clear every WooCommerce mission" },
  { id: "hard", name: "Deep diver", desc: "Complete a Hard mission" },
  { id: "talk", name: "Join the thread", desc: "Leave your first comment" },
  { id: "all", name: "Completionist", desc: "Complete every mission" },
];
