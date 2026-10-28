import { useEffect, useLayoutEffect, useRef } from "react";
import { useLocation, useNavigate, useNavigationType } from "react-router";
import { config } from "../lib/config.js";
import { loadView, matchRoute, toRoutePath } from "../lib/routes.js";

/*
 * Turns every same-site <a> into a client-side navigation: header links, mission rows,
 * search results, and links inside post content alike. Also prefetches posts on hover
 * and keeps scroll positions per history entry. Renders nothing.
 */
export default function SpaLinks() {
  const navigate = useNavigate();
  const location = useLocation();
  const navigationType = useNavigationType();
  const positions = useRef(new Map());
  const first = useRef(true);

  useEffect(() => {
    if (!config.spa) return;

    const linkFrom = (e) => {
      const a = e.target instanceof Element ? e.target.closest("a[href]") : null;
      if (!a || (a.target && a.target !== "_self") || a.hasAttribute("download") || "reload" in a.dataset) return null;
      return a;
    };

    const onClick = (e) => {
      if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
      const a = linkFrom(e);
      const path = a && toRoutePath(a.href);
      if (path == null) return;
      // Same page, different #hash: let the browser jump.
      const url = new URL(a.href);
      if (url.hash && url.pathname === window.location.pathname && url.search === window.location.search) return;
      e.preventDefault();
      navigate(path);
    };

    const onHover = (e) => {
      const a = linkFrom(e);
      const path = a && toRoutePath(a.href);
      if (path == null) return;
      const url = new URL(path, "http://x");
      const route = matchRoute({ pathname: url.pathname, search: url.search });
      if (route.view === "remote") loadView(route.url).catch(() => {});
    };

    document.addEventListener("click", onClick);
    document.addEventListener("mouseover", onHover, { passive: true });
    return () => {
      document.removeEventListener("click", onClick);
      document.removeEventListener("mouseover", onHover);
    };
  }, [navigate]);

  // Remember where each history entry was scrolled to.
  useEffect(() => {
    if (!config.spa) return;
    if ("scrollRestoration" in history) history.scrollRestoration = "manual";
    const key = location.key;
    const onScroll = () => positions.current.set(key, window.scrollY);
    window.addEventListener("scroll", onScroll, { passive: true });
    return () => window.removeEventListener("scroll", onScroll);
  }, [location.key]);

  // New page: top (or its #hash). Back/forward: where the reader left it.
  useLayoutEffect(() => {
    if (first.current) {
      first.current = false;
      return;
    }
    if (navigationType === "POP") {
      window.scrollTo(0, positions.current.get(location.key) ?? 0);
      return;
    }
    const target = location.hash && document.getElementById(decodeURIComponent(location.hash.slice(1)));
    if (target) target.scrollIntoView();
    else window.scrollTo(0, 0);
  }, [location.key, location.hash, navigationType]);

  return null;
}
