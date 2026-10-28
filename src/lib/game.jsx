import { createContext, useCallback, useContext, useEffect, useMemo, useReducer } from "react";
import { config, STORAGE_KEY, TROPHIES } from "./config.js";
import { levelOf, quizRight, rankOf, trophyChecks, xpOf } from "./progress.js";
import { useData } from "./data.js";

/*
 * Game state, persisted to localStorage[STORAGE_KEY] as
 * { done, quiz, ach, comments, liked, cName, cEmail }:
 *   done      post IDs cleared
 *   quiz      { [postId]: "right" | "wrong", [postId + ":pick"]: optionIndex }
 *   ach       unlocked trophy IDs
 *   comments  { [postId]: [{ id, name, text, date }] } comments this reader posted
 *   liked     comment like keys ("c:<id>")
 *   cName, cEmail  remembered comment-form identity
 */

const empty = { done: [], quiz: {}, ach: [], comments: {}, liked: [], cName: "", cEmail: "" };
const initial = { ...empty, hydrated: false, toast: null };

const GameContext = createContext(null);

function load() {
  try {
    const saved = JSON.parse(localStorage.getItem(STORAGE_KEY) || "null");
    return saved && typeof saved === "object" ? saved : {};
  } catch {
    return {};
  }
}

function save(state) {
  try {
    const { done, quiz, ach, comments, liked, cName, cEmail } = state;
    localStorage.setItem(STORAGE_KEY, JSON.stringify({ done, quiz, ach, comments, liked, cName, cEmail }));
  } catch {
    // Private mode or blocked storage: progress just won't survive a reload.
  }
}

// ---- Reducer ----

// Apply a progress change, then award trophies and pick the toast:
// level up > trophy unlocked > the action's own message.
function commit(state, posts, patch, title, sub) {
  const next = { ...state, ...patch };
  const xpBefore = xpOf(posts, state);
  const xpAfter = xpOf(posts, next);
  const checks = trophyChecks(posts, next);
  const newly = TROPHIES.filter((t) => checks[t.id] && !state.ach.includes(t.id));
  next.ach = [...state.ach, ...newly.map((t) => t.id)];

  let toast = { title, sub };
  if (newly.length) toast = { title: "Trophy unlocked: " + newly[0].name, sub };
  if (levelOf(xpAfter) > levelOf(xpBefore)) {
    const level = levelOf(xpAfter);
    toast = { title: "Level " + level + " · " + rankOf(level), sub: sub + " · Level up" };
  }
  next.toast = { ...toast, id: Date.now() };
  return next;
}

// Guards live here rather than in the callbacks, so repeated dispatches
// (scroll events, double clicks) before a re-render can't award XP twice.
function reducer(state, action) {
  switch (action.type) {
    case "hydrate":
      return { ...state, ...empty, ...action.saved, hydrated: true };

    case "complete": {
      const { post, posts } = action;
      if (!state.hydrated || state.done.includes(post.id)) return state;
      return commit(state, posts, { done: [...state.done, post.id] }, "Mission complete", "+" + post.xp + " XP");
    }

    case "answer": {
      const { post, index, posts } = action;
      if (state.quiz[post.id]) return state;
      const ok = index === post.quiz.answer;
      const quiz = { ...state.quiz, [post.id]: ok ? "right" : "wrong", [post.id + ":pick"]: index };
      return ok ? commit(state, posts, { quiz }, "Checkpoint cleared", "+" + config.quizXp + " XP bonus") : { ...state, quiz };
    }

    case "comment": {
      const { postId, comment, posts } = action;
      const mine = state.comments[postId] ?? [];
      const comments = { ...state.comments, [postId]: [...mine, comment] };
      return mine.length === 0 ? commit(state, posts, { comments }, "Comment posted", "+" + config.commentXp + " XP") : { ...state, comments };
    }

    case "like": {
      const liked = state.liked.includes(action.key) ? state.liked.filter((k) => k !== action.key) : [...state.liked, action.key];
      return { ...state, liked };
    }

    case "patch":
      return { ...state, ...action.patch };

    case "clearToast":
      return state.toast && state.toast.id === action.id ? { ...state, toast: null } : state;

    case "reset":
      return { ...state, done: [], quiz: {}, ach: [], comments: {}, liked: [], toast: null };

    default:
      return state;
  }
}

export function GameProvider({ children }) {
  const { posts } = useData();
  const [state, dispatch] = useReducer(reducer, initial);

  // Read storage after mount so the first render matches the server-less initial state.
  useEffect(() => {
    dispatch({ type: "hydrate", saved: load() });
  }, []);

  useEffect(() => {
    if (state.hydrated) save(state);
  }, [state]);

  useEffect(() => {
    if (!state.toast) return;
    const id = state.toast.id;
    const timer = setTimeout(() => dispatch({ type: "clearToast", id }), 3200);
    return () => clearTimeout(timer);
  }, [state.toast]);

  const complete = useCallback((post) => dispatch({ type: "complete", post, posts }), [posts]);
  const answer = useCallback((post, index) => dispatch({ type: "answer", post, index, posts }), [posts]);
  const addComment = useCallback((postId, comment) => dispatch({ type: "comment", postId, comment, posts }), [posts]);
  const toggleLike = useCallback((key) => dispatch({ type: "like", key }), []);
  const setIdentity = useCallback((patch) => dispatch({ type: "patch", patch }), []);

  const reset = useCallback(() => {
    try {
      localStorage.removeItem(STORAGE_KEY);
    } catch {
      // Ignore blocked storage.
    }
    dispatch({ type: "reset" });
  }, []);

  const value = useMemo(() => {
    const xp = xpOf(posts, state);
    const level = levelOf(xp);
    const xpInLevel = xp % config.xpPerLevel;
    return {
      ...state,
      xp,
      level,
      rank: rankOf(level),
      nextRank: rankOf(level + 1),
      xpInLevel,
      toNext: config.xpPerLevel - xpInLevel,
      quizCorrect: quizRight(state.quiz),
      complete,
      answer,
      addComment,
      toggleLike,
      setIdentity,
      reset,
    };
  }, [posts, state, complete, answer, addComment, toggleLike, setIdentity, reset]);

  return <GameContext.Provider value={value}>{children}</GameContext.Provider>;
}

export function useGame() {
  return useContext(GameContext);
}
