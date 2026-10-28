import { useEffect, useState } from "react";
import { BrowserRouter, useLocation } from "react-router";
import { useData } from "./lib/data.js";
import { basename, matchRoute, serverRoute, useDocumentTitle, useRemoteView } from "./lib/routes.js";
import SpaLinks from "./components/SpaLinks.jsx";
import BackToTop from "./components/BackToTop.jsx";
import Header from "./sections/Header.jsx";
import Board from "./sections/Board.jsx";
import PostView from "./sections/PostView.jsx";
import PageView from "./sections/PageView.jsx";
import ViewSkeleton from "./sections/ViewSkeleton.jsx";
import Footer from "./sections/Footer.jsx";
import Toast from "./sections/Toast.jsx";
import TrophyModal from "./sections/TrophyModal.jsx";
import SearchModal from "./sections/SearchModal.jsx";

export default function App() {
  return (
    <BrowserRouter basename={basename}>
      <Shell />
    </BrowserRouter>
  );
}

// Header, footer and overlays stay mounted across navigations, so toasts and XP animations carry over.
function Shell() {
  const { search } = useData();
  const location = useLocation();
  const [trophiesOpen, setTrophiesOpen] = useState(false);
  // A WordPress ?s= search lands on the board with the search modal open.
  const [searchQuery, setSearchQuery] = useState(search ? search : null);

  useEffect(() => {
    const onKey = (e) => {
      if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === "k") {
        e.preventDefault();
        setSearchQuery("");
      }
      if (e.key === "Escape") {
        setSearchQuery(null);
        setTrophiesOpen(false);
      }
    };
    window.addEventListener("keydown", onKey);
    return () => window.removeEventListener("keydown", onKey);
  }, []);

  // Close overlays when the page changes (e.g. after picking a search result).
  useEffect(() => {
    setSearchQuery(null);
    setTrophiesOpen(false);
  }, [location.pathname, location.search]);

  return (
    <div className="min-h-screen bg-bg bg-[image:radial-gradient(1200px_500px_at_0%_-10%,color-mix(in_srgb,var(--color-accent-900)_90%,transparent),transparent_70%)] bg-no-repeat font-body text-text">
      <SpaLinks />
      <Header onOpenTrophies={() => setTrophiesOpen(true)} onOpenSearch={() => setSearchQuery("")} />
      <View />
      <Footer />
      <Toast />
      <BackToTop />
      {trophiesOpen && <TrophyModal onClose={() => setTrophiesOpen(false)} />}
      {searchQuery !== null && <SearchModal initialQuery={searchQuery} onClose={() => setSearchQuery(null)} />}
    </div>
  );
}

function View() {
  const { site } = useData();
  const location = useLocation();
  // "default" is the history entry WordPress rendered; later entries come from client navigation.
  const route = location.key === "default" ? serverRoute() : matchRoute(location);

  if (route.view === "home") return <BoardView key="home" title={site.homeTitle} />;
  if (route.view === "category") {
    return <BoardView key={"cat-" + route.category.id} categoryId={route.category.id} title={`${route.category.name} ${site.titleSep} ${site.title}`} />;
  }
  return <RemoteView key={route.url} url={route.url} />;
}

function BoardView({ categoryId = null, title }) {
  useDocumentTitle(title);
  return <Board categoryId={categoryId} />;
}

// Posts and pages, loaded from WordPress by URL.
function RemoteView({ url }) {
  const state = useRemoteView(url);
  const result = state.result;
  useDocumentTitle(result?.title);

  // Not a post or page (a tag archive, a date archive…): let WordPress render it. The address bar
  // already shows this URL, so clear the router's history state first; the reloaded page is then the
  // "default" entry and renders WordPress's own view instead of trying this lookup again.
  useEffect(() => {
    if (state.status === "ready" && !result) {
      window.history.replaceState(null, "", url);
      window.location.reload();
    }
  }, [state.status, result, url]);

  if (state.status === "error") return <PageView notFound error />;
  if (state.status !== "ready" || !result) return <ViewSkeleton />;
  if (result.view === "post") return <PostView key={result.post.id} post={result.post} />;
  return <PageView post={result.post} notFound={result.view === "404"} />;
}
