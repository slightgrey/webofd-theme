import { useMemo, useState } from "react";
import { CheckCircle, Circle, Lightning, X } from "@phosphor-icons/react";
import { config } from "../lib/config.js";
import { useData } from "../lib/data.js";
import { useGame } from "../lib/game.jsx";
import { quizRight, shopTrack, topicsCleared } from "../lib/progress.js";
import { Button, Card, Kicker, Tag } from "../components/ui.jsx";
import { cx } from "../lib/cx.js";

const FILTERS = [
  ["all", "All"],
  ["open", "Open"],
  ["cleared", "Cleared"],
];

export default function Board({ categoryId = null }) {
  const { posts, categories } = useData();
  const { done } = useGame();
  const [topic, setTopic] = useState(categoryId);
  const [filter, setFilter] = useState("all");

  const topicName = categories.find((c) => c.id === topic)?.name ?? "";
  const list = posts.filter(
    (p) => (!topic || p.categories.includes(topic)) && (filter === "all" || (filter === "open") !== done.includes(p.id))
  );

  return (
    <main className="mx-auto max-w-[1320px] px-6 pt-16 pb-24 max-sm:px-4 max-sm:pt-10">
      <Hero />
      <SkillTree topic={topic} onPick={setTopic} />

      <section className="flex flex-wrap items-start gap-10">
        <div className="min-w-0 flex-[1_1_640px]">
          <div className="mb-2 flex flex-wrap items-center justify-between gap-3">
            <h6 className="m-0 text-neutral-500">{topic ? topicName + " missions" : "Mission board"}</h6>
            <div className="flex gap-1.5">
              {FILTERS.map(([key, label]) => (
                <Button key={key} variant={filter === key ? "primary" : "secondary"} onClick={() => setFilter(key)} className="px-2.5 py-[5px] text-[12px]">
                  {label}
                </Button>
              ))}
            </div>
          </div>
          {list.length === 0 && <div className="py-10 text-neutral-500">No missions match this filter.</div>}
          <div className="flex flex-col">
            {list.map((p) => (
              <MissionRow key={p.id} post={p} />
            ))}
          </div>
        </div>
        <Quests />
      </section>
    </main>
  );
}

function Hero() {
  const { site, posts } = useData();
  const { xp, level, rank, nextRank, toNext, done, quizCorrect, achCount } = useGame();
  const cleared = posts.filter((p) => done.includes(p.id)).length;

  const stats = [
    [cleared, "/" + posts.length, "Missions"],
    [quizCorrect, null, "Quizzes aced"],
    [achCount, null, "Trophies"],
  ];

  return (
    <section className="mb-14 grid grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)] items-end gap-12 max-lg:grid-cols-1 max-lg:gap-8">
      <div>
        <div className="mb-3.5 text-[11px] tracking-[0.12em] text-accent uppercase">{site.eyebrow}</div>
        <h1 className="m-0 mb-4 text-[50px] leading-[1.05] tracking-[-0.025em] text-wrap-balance max-sm:text-[36px]">{site.heading}</h1>
        <p className="m-0 max-w-[540px] text-[16px] leading-[1.6] text-neutral-400 text-wrap-pretty">{site.intro}</p>
      </div>
      <Card className="gap-3.5 p-5">
        <div className="flex items-center justify-between">
          <Kicker>Your run</Kicker>
          <span className="text-[12px] text-neutral-500">{xp} XP total</span>
        </div>
        <div className="flex items-center gap-3.5">
          <span className="grid size-[52px] place-items-center rounded-md bg-accent-900 text-[22px] text-accent-200 shadow-[inset_0_0_0_1px_var(--color-accent),0_0_24px_color-mix(in_srgb,var(--color-accent)_25%,transparent)]">
            {level}
          </span>
          <div className="flex-1">
            <div className="text-[17px]">{rank}</div>
            <div className="text-[12px] text-neutral-500">
              {toNext} XP to {nextRank}
            </div>
          </div>
        </div>
        <div className="grid grid-cols-3 gap-2">
          {stats.map(([value, suffix, label]) => (
            <div key={label} className="rounded-md bg-bg p-2.5">
              <div className="text-[20px]">
                {value}
                {suffix && <span className="text-[12px] text-neutral-500">{suffix}</span>}
              </div>
              <div className="text-[11px] text-neutral-500">{label}</div>
            </div>
          ))}
        </div>
      </Card>
    </section>
  );
}

function SkillTree({ topic, onPick }) {
  const { posts, categories } = useData();
  const { done } = useGame();
  const [showAll, setShowAll] = useState(false);
  const limit = config.skillsShown;

  const topicName = categories.find((c) => c.id === topic)?.name ?? "";
  const selectedIndex = categories.findIndex((c) => c.id === topic);
  const shown = showAll ? categories : categories.slice(0, limit).concat(selectedIndex >= limit ? [categories[selectedIndex]] : []);

  // At most `skillsShown` cards per row, never narrower than 160px.
  const gap = 10;
  const columns = `repeat(auto-fill, minmax(max(160px, calc((100% - ${(limit - 1) * gap}px) / ${limit})), 1fr))`;

  return (
    <section className="mb-14">
      <div className="mb-3.5 flex min-h-7 items-center justify-between gap-3">
        <h6 className="m-0 text-neutral-500">Skill tree</h6>
        <div className="flex items-center gap-1">
          {topic && (
            <Button variant="ghost" onClick={() => onPick(null)} className="gap-1.5 px-2.5 py-1 text-[12px]">
              <X size={12} />
              Clear filter · {topicName}
            </Button>
          )}
          {categories.length > limit && (
            <Button variant="ghost" onClick={() => setShowAll(!showAll)} className="px-2.5 py-1 text-[12px]">
              {showAll ? "Show fewer" : "Show all " + categories.length}
            </Button>
          )}
        </div>
      </div>
      <div className="grid gap-2.5" style={{ gridTemplateColumns: columns }}>
        {shown.map((cat) => (
          <SkillCard key={cat.id} cat={cat} posts={posts} done={done} selected={topic === cat.id} onPick={() => onPick(topic === cat.id ? null : cat.id)} />
        ))}
      </div>
    </section>
  );
}

function SkillCard({ cat, posts, done, selected, onPick }) {
  const all = posts.filter((p) => p.categories.includes(cat.id));
  const cleared = all.filter((p) => done.includes(p.id)).length;
  const mastered = all.length > 0 && cleared === all.length;

  return (
    <button
      type="button"
      onClick={onPick}
      aria-pressed={selected}
      className={cx(
        "flex flex-col gap-2.5 rounded-md border-0 bg-surface p-3.5 text-left font-[inherit] leading-[inherit] text-text transition-shadow duration-140 hover:shadow-[0_0_0_1px_var(--color-accent-600)]",
        selected ? "shadow-[0_0_0_1px_var(--color-accent)]" : "shadow-sm"
      )}
    >
      <div className="flex w-full items-center justify-between">
        <span className="text-[14px]">{cat.name}</span>
        <span className={cx("text-[11px]", mastered ? "text-accent-300" : "text-neutral-500")}>{mastered ? "Mastered" : cleared + "/" + all.length}</span>
      </div>
      <div className="flex w-full gap-[3px]">
        {all.map((p, i) => (
          <span key={p.id} className={cx("h-1 flex-1 rounded-[2px]", i < cleared ? "bg-accent shadow-[0_0_6px_var(--color-accent)]" : "bg-neutral-800")} />
        ))}
      </div>
    </button>
  );
}

function quizLabel(post, quiz) {
  if (!post.quiz) return "";
  const state = quiz[post.id];
  return state === "right" ? "Quiz aced" : state === "wrong" ? "Quiz missed" : "Quiz +" + config.quizXp;
}

function MissionRow({ post }) {
  const { done, quiz } = useGame();
  const cleared = done.includes(post.id);

  return (
    <a
      href={post.url}
      className="-mx-3 grid grid-cols-[28px_168px_minmax(0,1fr)_auto] items-start gap-[18px] rounded-md px-3 py-[18px] text-text no-underline row-rule transition-[background-color] duration-140 hover:bg-[color-mix(in_srgb,var(--color-accent)_6%,transparent)] hover:text-text max-sm:grid-cols-[22px_minmax(0,1fr)] max-sm:gap-3"
    >
      <div className={cx("flex h-[105px] items-center justify-center self-stretch max-sm:h-auto max-sm:items-start max-sm:pt-0.5", cleared ? "text-accent" : "text-neutral-700")}>
        {cleared ? <CheckCircle size={22} weight="fill" aria-label="Cleared" /> : <Circle size={22} aria-label="Open" />}
      </div>
      <div className="relative h-[105px] w-[168px] overflow-hidden rounded-md bg-surface shadow-sm mix-blend-lighten max-sm:hidden">
        {post.image ? (
          <img src={post.image} alt="" width={168} height={105} loading="lazy" decoding="async" className="size-full rounded-md object-cover" />
        ) : (
          <span className="block size-full bg-[linear-gradient(135deg,var(--color-accent-900),var(--color-surface)_70%)]" />
        )}
      </div>
      <div>
        <div className="mb-1.5 flex flex-wrap items-center gap-2.5">
          <span className="text-[10px] tracking-[0.1em] text-accent uppercase">{post.topic}</span>
          <Tag size="sm">{post.diff}</Tag>
          <span className="text-[11px] text-neutral-600">
            {post.readMinutes} min · {post.date}
          </span>
        </div>
        <h3 className="m-0 mb-1.5 text-[19px] text-wrap-balance">{post.title}</h3>
        <p className="m-0 max-w-[600px] text-[14px] text-neutral-400 text-wrap-pretty">{post.excerpt}</p>
        <div className="mt-2 hidden text-[13px] max-sm:block">
          <span className={cleared ? "text-neutral-600" : "text-accent-300"}>{cleared ? "Cleared" : "+" + post.xp + " XP"}</span>
        </div>
      </div>
      <div className="flex flex-col items-end gap-1 pt-0.5 max-sm:hidden">
        <span className={cx("flex items-center gap-1 text-[13px]", cleared ? "text-neutral-600" : "text-accent-300")}>
          <Lightning size={12} weight="fill" />
          {cleared ? "Cleared" : "+" + post.xp + " XP"}
        </span>
        <span className="text-[11px] text-neutral-600">{quizLabel(post, quiz)}</span>
      </div>
    </a>
  );
}

function Quests() {
  const { posts } = useData();
  const { done, quiz } = useGame();

  const quests = useMemo(() => {
    const shop = shopTrack(posts);
    const list = [
      ["Branch out", "Clear missions in 3 different topics", topicsCleared(posts, done), 3, "Full stack"],
      ["Pop quiz", "Ace 3 checkpoint quizzes", quizRight(quiz), 3, "Code reviewer"],
    ];
    if (shop) {
      list.push([shop.quest, shop.desc, shop.posts.filter((p) => done.includes(p.id)).length, shop.posts.length, shop.trophy]);
    }
    return list;
  }, [posts, done, quiz]);

  return (
    <aside className="sticky top-[calc(var(--wp-admin--admin-bar--height,0px)+88px)] flex max-w-[320px] flex-[1_0_280px] flex-col gap-3 max-md:static max-md:max-w-none">
      <h6 className="m-0 mb-0.5 text-neutral-500">Quests</h6>
      {quests.map(([name, desc, n, max, reward]) => {
        const complete = n >= max;
        return (
          <Card key={name} className="gap-2 p-3.5">
            <div className="flex items-baseline justify-between gap-2">
              <span className="text-[14px]">{name}</span>
              <span className={cx("text-[11px]", complete ? "text-accent-300" : "text-neutral-500")}>{complete ? "Complete" : reward}</span>
            </div>
            <div className="text-[12px] text-neutral-500">{desc}</div>
            <div className="flex items-center gap-2">
              <div className="h-1 flex-1 overflow-hidden rounded-[2px] bg-neutral-800">
                <div className="h-1 bg-accent transition-[width] duration-600" style={{ width: Math.min(100, (n / max) * 100) + "%" }} />
              </div>
              <span className="text-[11px] text-neutral-400">
                {Math.min(n, max)}/{max}
              </span>
            </div>
          </Card>
        );
      })}
    </aside>
  );
}
