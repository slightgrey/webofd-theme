// Pure game maths, shared by the reducer in game.jsx and the UI.
import { config, RANKS } from "./config.js";

export const levelOf = (xp) => Math.floor(xp / config.xpPerLevel) + 1;
export const rankOf = (level) => RANKS[Math.min(RANKS.length - 1, level - 1)];
export const quizRight = (quiz) => Object.values(quiz).filter((v) => v === "right").length;
const commentedPosts = (comments) => Object.values(comments).filter((l) => l && l.length).length;

export function xpOf(posts, { done, quiz, comments }) {
  const byId = new Map(posts.map((p) => [p.id, p]));
  return (
    done.reduce((sum, id) => sum + (byId.get(id)?.xp ?? 0), 0) +
    quizRight(quiz) * config.quizXp +
    commentedPosts(comments) * config.commentXp
  );
}

export function topicsCleared(posts, done) {
  return new Set(posts.filter((p) => done.includes(p.id)).map((p) => p.topic)).size;
}

export function shopPosts(posts) {
  return posts.filter((p) => p.cats.some((c) => c.slug === config.shopCategorySlug));
}

export function trophyChecks(posts, { done, quiz, comments }) {
  const right = quizRight(quiz);
  const shop = shopPosts(posts);
  const cleared = posts.filter((p) => done.includes(p.id));
  return {
    first: cleared.length >= 1,
    quiz1: right >= 1,
    quiz3: right >= 3,
    explorer: topicsCleared(posts, done) >= 3,
    woo: shop.length > 0 && shop.every((p) => done.includes(p.id)),
    hard: cleared.some((p) => p.diff === "Hard"),
    talk: commentedPosts(comments) > 0,
    all: posts.length > 0 && cleared.length === posts.length,
  };
}
