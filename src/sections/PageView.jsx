import parse from "html-react-parser";
import { ArrowLeft } from "@phosphor-icons/react";
import { useData } from "../lib/data.js";
import { Button } from "../components/ui.jsx";

// Static pages and the 404 screen: same chrome as a post, without the game layer.
export default function PageView({ post, notFound = !post, error = false }) {
  const { site } = useData();

  return (
    <main className="mx-auto max-w-[1320px] px-6 pt-10 pb-24 max-sm:px-4">
      <article className="max-w-[780px]">
        <Button as="a" variant="ghost" href={site.homeUrl} className="mb-8 flex w-fit items-center gap-2">
          <ArrowLeft size={14} />
          Mission board
        </Button>
        <h1 className="m-0 mb-[18px] text-[44px] leading-[1.08] tracking-[-0.025em] text-wrap-balance max-sm:text-[32px]">{error ? "Connection lost" : notFound ? "Mission not found" : post.title}</h1>
        {notFound ? (
          <p className="m-0 text-[19px] leading-[1.55] text-neutral-300">
            {error ? "This page couldn't be loaded. Check your connection and try again." : "This page doesn't exist. Head back to the board and pick another mission."}
          </p>
        ) : (
          <>
            <div className="mb-10 h-px rule-fade" />
            <div className="prose-wod">{parse(post.contentHtml ?? "")}</div>
          </>
        )}
      </article>
    </main>
  );
}
