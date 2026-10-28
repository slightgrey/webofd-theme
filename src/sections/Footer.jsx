import { useData } from "../lib/data.js";

const link = "text-neutral-400 no-underline hover:text-accent";

export default function Footer() {
  const { site } = useData();

  return (
    <footer className="mx-auto flex max-w-[1320px] flex-wrap justify-between gap-4 px-6 pt-6 pb-10 text-[12px] text-neutral-500 max-sm:px-4">
      <span>
        © {new Date().getFullYear()} {site.owner}
      </span>
      <div className="flex gap-4">
        {site.githubUrl && (
          <a href={site.githubUrl} target="_blank" rel="noopener" className={link}>
            GitHub
          </a>
        )}
        <a href={site.feedUrl} target="_blank" rel="noopener" className={link}>
          RSS
        </a>
      </div>
    </footer>
  );
}
