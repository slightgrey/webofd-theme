import { useEffect, useState } from "react";
import { appData, enrich } from "./data.js";

const { site, categories, rest, view: initialView, categoryId: initialCategoryId, post: initialPost } = appData;

const home = new URL(site.homeUrl ?? "/", window.location.href);

// The router runs under the WordPress home path, so sub-directory installs work too.
export const basename = home.pathname.replace(/\/$/, "") || "/";

const trimSlash = (path) => (path.length > 1 ? path.replace(/\/$/, "") : path);

// Query strings WordPress uses for content with plain permalinks; any other query means a full page load.
const ROUTE_PARAMS = ["p", "page_id", "cat"];

/**
 * Router path ("/my-post/") for a link on this site, or null when it has to be a normal page load
 * (other origins, wp-admin, feeds, files, searches).
 */
export function toRoutePath(href) {
  const url = new URL(href, window.location.href);
  if (url.origin !== home.origin) return null;

  const prefix = basename === "/" ? "" : basename;
  if (prefix && url.pathname !== prefix && !url.pathname.startsWith(prefix + "/")) return null;
  const path = url.pathname.slice(prefix.length) || "/";

  if (/^\/(wp-admin|wp-login\.php|wp-content|wp-includes|wp-json)(\/|$)/.test(path)) return null;
  if (/\/feed\/?$/.test(path) || /\.(?!html?$)[a-z0-9]{2,5}$/i.test(path)) return null;
  if ([...url.searchParams.keys()].some((key) => !ROUTE_PARAMS.includes(key))) return null;

  return path + url.search + url.hash;
}

/**
 * Only the query parameters that pick content. Tracking and cache-busting extras
 * (?utm_source=…, ?fbclid=…, ?no-cache) must not turn the home page into an unknown URL.
 */
function routeSearch(search) {
  const all = new URLSearchParams(search);
  const kept = new URLSearchParams();
  for (const key of ROUTE_PARAMS) if (all.has(key)) kept.set(key, all.get(key));
  const query = kept.toString();
  return query ? "?" + query : "";
}

const categoryByPath = new Map(
  categories.map((c) => {
    const path = toRoutePath(c.url);
    return [path ? trimSlash(path) : c.url, c];
  })
);

/**
 * What a router location shows: the board, a category board, or something to load from WordPress.
 */
export function matchRoute({ pathname, search }) {
  search = routeSearch(search);
  const key = trimSlash(pathname) + search;
  if (key === "/") return { view: "home" };

  const params = new URLSearchParams(search);
  const category = categoryByPath.get(key) ?? categories.find((c) => String(c.id) === params.get("cat"));
  if (category) return { view: "category", category };

  return { view: "remote", url: home.origin + (basename === "/" ? "" : basename) + pathname + search };
}

/**
 * What WordPress rendered for the page that was actually loaded. Trusted over matchRoute() for
 * that first history entry, so URLs the app can't resolve itself (tag and date archives, extra
 * query strings) never bounce between "not found" and a reload.
 */
export function serverRoute() {
  if (initialView === "home") return { view: "home" };
  if (initialView === "category") {
    const category = categories.find((c) => c.id === initialCategoryId);
    if (category) return { view: "category", category };
  }
  return { view: "remote", url: currentUrl };
}

/**
 * REST URL that works with pretty and plain permalinks (rest_url() is "/?rest_route=/" for the latter).
 */
export function restUrl(route, params = {}) {
  const url = new URL(rest.root, window.location.href);
  if (url.searchParams.has("rest_route")) {
    url.searchParams.set("rest_route", url.searchParams.get("rest_route").replace(/\/?$/, "/") + route);
  } else {
    url.pathname = url.pathname.replace(/\/?$/, "/") + route;
  }
  for (const [key, value] of Object.entries(params)) url.searchParams.set(key, value);
  return url.toString();
}

// ---- Posts and pages loaded by URL ----

// url -> settled result, so revisits and back/forward render synchronously.
const settled = new Map();
// url -> in-flight promise, shared by hover prefetch and navigation.
const pending = new Map();

// The page WordPress rendered is already in window.wodData.
const currentUrl = window.location.origin + window.location.pathname + routeSearch(window.location.search);
if (initialPost) settled.set(currentUrl, { view: initialView, title: document.title, post: initialPost });
else if (initialView === "404") settled.set(currentUrl, { view: "404", title: document.title, post: null });

/**
 * Load a post or page by front-end URL. Resolves to null when WordPress has no post/page there.
 */
export function loadView(url) {
  if (settled.has(url)) return Promise.resolve(settled.get(url));
  if (!pending.has(url)) {
    const request = fetch(restUrl("wod/v1/view", { url }), { headers: { "X-WP-Nonce": rest.nonce }, credentials: "same-origin" })
      .then(async (res) => {
        if (res.status === 404) return null;
        if (!res.ok) throw new Error("Request failed: " + res.status);
        const json = await res.json();
        return { view: json.view, title: json.title, post: enrich(json.post) };
      })
      .then((result) => {
        settled.set(url, result);
        return result;
      })
      .finally(() => pending.delete(url));
    pending.set(url, request);
  }
  return pending.get(url);
}

export function useRemoteView(url) {
  const [state, setState] = useState(() => (settled.has(url) ? { url, status: "ready", result: settled.get(url) } : { url, status: "loading" }));

  useEffect(() => {
    if (settled.has(url)) {
      setState({ url, status: "ready", result: settled.get(url) });
      return;
    }
    let live = true;
    setState({ url, status: "loading" });
    loadView(url).then(
      (result) => live && setState({ url, status: "ready", result }),
      () => live && setState({ url, status: "error" })
    );
    return () => {
      live = false;
    };
  }, [url]);

  return state.url === url ? state : { url, status: "loading" };
}

export function useDocumentTitle(title) {
  useEffect(() => {
    if (title) document.title = title;
  }, [title]);
}
