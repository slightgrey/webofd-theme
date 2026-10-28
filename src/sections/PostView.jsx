import { useEffect, useMemo, useState } from "react";
import parse from "html-react-parser";
import { ArrowLeft } from "@phosphor-icons/react";
import { config } from "../lib/config.js";
import { useData, xpLabel } from "../lib/data.js";
import { useGame } from "../lib/game.jsx";
import { Button, Card, Kicker, Tag } from "../components/ui.jsx";
import { cx } from "../lib/cx.js";
import CodeBlock from "../components/CodeBlock.jsx";
import FeaturedImage from "../components/FeaturedImage.jsx";
import Comments from "./Comments.jsx";

export default function PostView({ post }) {
  const { posts, site } = useData();
  const { progress, active } = useReadingProgress(post);

  return (
    <>
      <div className="fixed inset-x-0 top-[var(--wp-admin--admin-bar--height,0px)] z-30 h-[3px]">
        <div className="h-[3px] bg-accent shadow-[0_0_12px_var(--color-accent)] transition-[width] duration-80 ease-linear" style={{ width: (progress * 100).toFixed(1) + "%" }} />
      </div>

      <main className="mx-auto grid max-w-[1320px] grid-cols-[minmax(0,1fr)_220px] gap-16 px-6 pt-10 pb-24 max-lg:grid-cols-1 max-sm:px-4">
        <article className="max-w-[780px]">
          <Button as="a" variant="ghost" href={site.homeUrl} className="mb-8 flex w-fit items-center gap-2">
            <ArrowLeft size={14} />
            Mission board
          </Button>
          <div className="mb-[18px] flex flex-wrap items-center gap-2">
            <Tag variant="accent">{post.topic}</Tag>
            <Tag variant="neutral">{post.diff}</Tag>
            <Tag variant="outline">+{post.xp} XP</Tag>
            <span className="text-[12px] text-neutral-500">
              {post.date} · {post.readMinutes} min read
            </span>
          </div>
          <h1 className="m-0 mb-[18px] text-[44px] leading-[1.08] tracking-[-0.025em] text-wrap-balance max-sm:text-[32px]">{post.title}</h1>
          {post.excerpt && <p className="m-0 mb-10 text-[19px] leading-[1.55] text-neutral-300 text-wrap-pretty">{post.excerpt}</p>}
          <FeaturedImage image={post.featured} />
          <div className="mb-10 h-px rule-fade" />

          <PostBody html={post.contentHtml} />

          {config.showQuiz && post.quiz && <Checkpoint post={post} />}
          <MissionBanner post={post} />
          <Comments post={post} />
          <NextMission post={post} posts={posts} />
        </article>

        <aside className="sticky top-[calc(var(--wp-admin--admin-bar--height,0px)+96px)] flex flex-col gap-6 self-start pt-[88px] max-lg:hidden">
          <div>
            <div className="mb-2.5 text-[11px] tracking-[0.1em] text-neutral-500 uppercase">Mission progress</div>
            <div className="text-[28px] text-accent-200">{Math.round(progress * 100)}%</div>
          </div>
          {config.showToc && post.toc.length > 0 && <Waypoints toc={post.toc} active={active ?? post.toc[0].id} />}
        </aside>
      </main>
    </>
  );
}

// Scroll progress, the H2 currently in view, and auto-complete at the end of the page.
function useReadingProgress(post) {
  const { complete } = useGame();
  const [state, setState] = useState({ progress: 0, active: null });

  useEffect(() => {
    const onScroll = () => {
      const h = document.documentElement.scrollHeight - window.innerHeight;
      const progress = h > 0 ? Math.min(1, window.scrollY / h) : 0;
      let active = null;
      for (const { id } of post.toc) {
        const el = document.getElementById(id);
        if (el && el.getBoundingClientRect().top < 160 + adminBarHeight()) active = id;
      }
      setState({ progress, active });
      if (config.autoComplete && progress > config.autoCompleteAt) complete(post);
    };
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
    window.addEventListener("resize", onScroll, { passive: true });
    return () => {
      window.removeEventListener("scroll", onScroll);
      window.removeEventListener("resize", onScroll);
    };
  }, [post, complete]);

  return state;
}

function textOf(node) {
  if (node.type === "text") return node.data;
  return (node.children ?? []).map(textOf).join("");
}

function languageOf(node) {
  const code = node.children?.find((c) => c.name === "code");
  const classes = [node.attribs?.class, code?.attribs?.class, node.attribs?.["data-lang"] && "language-" + node.attribs["data-lang"]].join(" ");
  const match = classes.match(/(?:language|lang)-([\w+#-]+)/);
  return match ? match[1] : "code";
}

function PostBody({ html }) {
  const content = useMemo(
    () =>
      parse(html ?? "", {
        replace(node) {
          if (node.type === "tag" && node.name === "pre") {
            return <CodeBlock lang={languageOf(node)} code={textOf(node).replace(/\n$/, "")} />;
          }
        },
      }),
    [html]
  );

  return <div className="prose-wod">{content}</div>;
}

function Checkpoint({ post }) {
  const { quiz, answer } = useGame();
  const state = quiz[post.id];
  const pick = quiz[post.id + ":pick"];

  return (
    <Card elev="md" className="mt-12 mb-5 gap-3.5 bg-[linear-gradient(160deg,var(--color-accent-900),var(--color-surface)_60%)] p-[22px]">
      <div className="flex items-center justify-between">
        <Kicker>Checkpoint</Kicker>
        <span className="text-[12px] text-accent-300">+{config.quizXp} XP bonus</span>
      </div>
      <div className="text-[19px] leading-[1.3]">{post.quiz.question}</div>
      <div className="flex flex-col gap-2">
        {post.quiz.options.map((text, i) => {
          const correct = state && i === post.quiz.answer;
          const wrong = state === "wrong" && i === pick;
          return (
            <Button
              key={i}
              variant="secondary"
              disabled={!!state}
              onClick={() => answer(post, i)}
              className={cx(
                "justify-start gap-2.5 px-3.5 py-2.5 text-left disabled:cursor-default disabled:opacity-100",
                correct && "text-accent-200 shadow-[inset_0_0_0_1px_var(--color-accent),0_0_18px_color-mix(in_srgb,var(--color-accent)_25%,transparent)]",
                wrong && "text-neutral-600 line-through"
              )}
            >
              <span className="grid size-5 flex-none place-items-center rounded-sm text-[11px] text-neutral-400 no-underline shadow-[inset_0_0_0_1px_var(--color-neutral-700)]">{"ABC"[i]}</span>
              {text}
            </Button>
          );
        })}
      </div>
      {state && (
        <div className={cx("text-[13px]", state === "right" ? "text-accent-300" : "text-neutral-400")}>
          {state === "right" ? "Correct. Bonus XP added." : "Not quite. The highlighted answer is the right one."}
        </div>
      )}
    </Card>
  );
}

function MissionBanner({ post }) {
  const { done, complete } = useGame();
  const isDone = done.includes(post.id);

  return (
    <div
      className={cx(
        "mb-12 flex items-center gap-3 rounded-md px-[18px] py-4",
        isDone ? "shadow-[0_0_0_1px_var(--color-accent-600),0_0_24px_color-mix(in_srgb,var(--color-accent)_15%,transparent)]" : "shadow-sm"
      )}
    >
      <div className="flex-1">
        <div className="text-[15px]">{isDone ? "Mission complete" : "Finish reading to clear this mission"}</div>
        <div className="text-[12px] text-neutral-500">
          {isDone ? "+" + post.xp + " XP earned" : config.autoComplete ? "Clears automatically at the end of the page" : "Mark it done when you finish"}
        </div>
      </div>
      {!isDone && (
        <Button variant="primary" onClick={() => complete(post)}>
          Complete · +{post.xp} XP
        </Button>
      )}
    </div>
  );
}

function NextMission({ post, posts }) {
  const { done } = useGame();
  const index = posts.findIndex((p) => p.id === post.id);
  const next = posts[(index + 1) % posts.length];
  if (!next || next.id === post.id) return null;

  return (
    <Card as="a" href={next.url} className="cursor-pointer p-5 text-text no-underline transition-shadow duration-140 hover:text-text hover:shadow-md">
      <Kicker>Next mission</Kicker>
      <div className="font-heading text-[20px] leading-[1.2] font-medium">{next.title}</div>
      <div className="flex items-center gap-1.5 text-[11px] text-[color-mix(in_srgb,var(--color-text)_50%,transparent)]">
        {next.topic} · {next.diff} · {xpLabel(next, done)}
      </div>
    </Card>
  );
}

// The WordPress admin bar (logged-in users) sits above the sticky header.
function adminBarHeight() {
  return parseFloat(getComputedStyle(document.documentElement).getPropertyValue("--wp-admin--admin-bar--height")) || 0;
}

function Waypoints({ toc, active }) {
  const go = (e, id) => {
    e.preventDefault();
    const el = document.getElementById(id);
    if (!el) return;
    window.scrollTo({ top: el.getBoundingClientRect().top + window.scrollY - 88 - adminBarHeight(), behavior: "smooth" });
    history.replaceState(null, "", "#" + id);
  };

  return (
    <nav aria-label="Waypoints">
      <div className="mb-3 text-[11px] tracking-[0.1em] text-neutral-500 uppercase">Waypoints</div>
      <div className="flex flex-col gap-0.5">
        {toc.map((item) => {
          const on = item.id === active;
          return (
            <a
              key={item.id}
              href={"#" + item.id}
              onClick={(e) => go(e, item.id)}
              aria-current={on ? "location" : undefined}
              className={cx(
                "py-1.5 pl-3 text-[13px] leading-[1.4] no-underline",
                on ? "text-text shadow-[inset_2px_0_0_var(--color-accent)] hover:text-text" : "text-neutral-500 shadow-[inset_2px_0_0_var(--color-neutral-800)] hover:text-neutral-300"
              )}
            >
              {item.text}
            </a>
          );
        })}
      </div>
    </nav>
  );
}
