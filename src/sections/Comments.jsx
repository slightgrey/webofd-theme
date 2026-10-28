import { useRef, useState } from "react";
import { Heart } from "@phosphor-icons/react";
import { config } from "../lib/config.js";
import { formatDate, initials, useData } from "../lib/data.js";
import { restUrl } from "../lib/routes.js";
import { useGame } from "../lib/game.jsx";
import { Button, Card, Tag } from "../components/ui.jsx";
import { cx } from "../lib/cx.js";

const AVATARS = ["bg-accent-800", "bg-neutral-800", "bg-accent-2-800", "bg-neutral-700"];

const input =
  "min-h-9 w-full rounded-md border border-divider bg-surface px-2.5 py-1.5 font-[inherit] text-[14px] text-text caret-accent hover:border-[color-mix(in_srgb,var(--color-text)_45%,transparent)] focus-visible:border-accent focus-visible:outline-offset-0 placeholder:text-neutral-600";

export default function Comments({ post }) {
  const { rest } = useData();
  const { comments: local, liked, cName, cEmail, addComment, toggleLike, setIdentity } = useGame();
  const [text, setText] = useState("");
  const [sending, setSending] = useState(false);
  const [error, setError] = useState("");
  const textarea = useRef(null);

  const mine = local[post.id] ?? [];
  const mineIds = new Set(mine.map((c) => c.id));
  const serverIds = new Set(post.comments.map((c) => c.id));

  // Approved comments from WordPress, plus this reader's own (some may still await moderation).
  const list = [
    ...post.comments.map((c) => ({ ...c, mine: mineIds.has(c.id) })),
    ...mine.filter((c) => !serverIds.has(c.id)).map((c) => ({ ...c, mine: true, isAuthor: !!c.author && c.author === post.author })),
  ];
  const joined = mine.length > 0;
  const disabled = sending || !cName.trim() || !cEmail.trim() || !text.trim();

  const submit = async (e) => {
    e.preventDefault();
    if (disabled) return;
    setSending(true);
    setError("");
    try {
      const res = await fetch(restUrl("wp/v2/comments"), {
        method: "POST",
        credentials: "same-origin",
        headers: { "Content-Type": "application/json", "X-WP-Nonce": rest.nonce },
        body: JSON.stringify({ post: post.id, author_name: cName.trim(), author_email: cEmail.trim(), content: text.trim() }),
      });
      const json = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(json.message || "Your comment couldn't be posted. Try again in a moment.");
      addComment(post.id, { id: json.id, name: json.author_name || cName.trim(), text: text.trim(), date: formatDate(), status: json.status, author: json.author || 0 });
      setText("");
    } catch (err) {
      setError(err.message);
    } finally {
      setSending(false);
    }
  };

  const reply = (name) => {
    setText("@" + name.split(" ")[0] + " ");
    const el = textarea.current;
    if (el) {
      el.focus();
      window.scrollTo({ top: el.getBoundingClientRect().top + window.scrollY - 200, behavior: "smooth" });
    }
  };

  return (
    <section id="comments" className="mb-14 scroll-mt-[90px]">
      <div className="mb-4 flex items-baseline justify-between gap-3">
        <h3 className="m-0 text-[22px]">
          {list.length} {list.length === 1 ? "comment" : "comments"}
        </h3>
        <span className={cx("text-[12px]", joined ? "text-neutral-500" : "text-accent-300")}>
          {joined ? "Joined · +" + config.commentXp + " XP earned" : "+" + config.commentXp + " XP for joining in"}
        </span>
      </div>

      {post.commentsOpen ? (
        <Card as="form" onSubmit={submit} className="mb-3 gap-2.5 p-4">
          <div className="flex flex-wrap gap-2.5">
            <input className={cx(input, "max-w-[260px]")} placeholder="Name" aria-label="Name" autoComplete="name" value={cName} onChange={(e) => setIdentity({ cName: e.target.value })} />
            <input className={cx(input, "max-w-[260px]")} type="email" placeholder="Email (not shown)" aria-label="Email" autoComplete="email" value={cEmail} onChange={(e) => setIdentity({ cEmail: e.target.value })} />
          </div>
          <textarea ref={textarea} className={cx(input, "min-h-[90px] resize-y")} placeholder="Add to the discussion…" aria-label="Comment" value={text} onChange={(e) => setText(e.target.value)} />
          <div className="flex flex-wrap items-center justify-between gap-3">
            <span className={cx("text-[12px]", error ? "text-accent-300" : "text-neutral-500")} role={error ? "alert" : undefined}>
              {error || "Be kind. Code in backticks."}
            </span>
            <Button variant="primary" type="submit" disabled={disabled}>
              {sending ? "Posting…" : "Post comment"}
            </Button>
          </div>
        </Card>
      ) : (
        <p className="mb-3 text-[13px] text-neutral-500">Comments are closed.</p>
      )}

      {list.map((c, i) => {
        const key = "c:" + c.id;
        const isLiked = liked.includes(key);
        return (
          <div key={key} className="grid grid-cols-[36px_minmax(0,1fr)] gap-3 py-[18px] row-rule">
            <span className={cx("grid size-9 place-items-center rounded-md text-[13px] text-neutral-200", c.isAuthor ? "bg-accent-700" : AVATARS[i % AVATARS.length])}>{initials(c.name)}</span>
            <div>
              <div className="mb-1 flex flex-wrap items-center gap-2">
                <span className="text-[14px]">{c.name}</span>
                {c.isAuthor && (
                  <Tag variant="accent" size="sm">
                    Author
                  </Tag>
                )}
                {c.mine && (
                  <Tag variant="outline" size="sm">
                    You
                  </Tag>
                )}
                <span className="text-[11px] text-neutral-600">
                  {c.date}
                  {c.status === "hold" && !serverIds.has(c.id) && " · Awaiting moderation"}
                </span>
              </div>
              <p className="m-0 mb-2 text-[14px] leading-[1.65] whitespace-pre-wrap text-neutral-200 text-wrap-pretty">{c.text}</p>
              <div className="flex gap-1">
                <Button variant="ghost" onClick={() => toggleLike(key)} aria-pressed={isLiked} className={cx("gap-[5px] px-2 py-0.5 text-[12px]", isLiked ? "text-accent" : "text-neutral-400")}>
                  <Heart size={13} weight={isLiked ? "fill" : "regular"} />
                  {isLiked ? 1 : 0}
                </Button>
                <Button variant="ghost" onClick={() => reply(c.name)} className="px-2 py-0.5 text-[12px] text-neutral-400">
                  Reply
                </Button>
              </div>
            </div>
          </div>
        );
      })}
    </section>
  );
}
