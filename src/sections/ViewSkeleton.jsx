// Placeholder while a post or page loads, laid out like the post view so nothing jumps.
export default function ViewSkeleton() {
  const block = "animate-pulse rounded-md bg-surface";

  return (
    <main aria-busy="true" aria-label="Loading" className="mx-auto grid max-w-[1320px] grid-cols-[minmax(0,1fr)_220px] gap-16 px-6 pt-10 pb-24 max-lg:grid-cols-1 max-sm:px-4">
      <div className="max-w-[780px]">
        <div className={`${block} mb-8 h-7 w-36`} />
        <div className="mb-[18px] flex gap-2">
          <div className={`${block} h-6 w-20`} />
          <div className={`${block} h-6 w-14`} />
          <div className={`${block} h-6 w-16`} />
        </div>
        <div className={`${block} mb-3 h-11 w-11/12`} />
        <div className={`${block} mb-[18px] h-11 w-2/3`} />
        <div className={`${block} mb-10 h-14 w-full`} />
        <div className="mb-10 h-px rule-fade" />
        {[0, 1, 2].map((i) => (
          <div key={i} className="mb-9">
            <div className={`${block} mb-3.5 h-7 w-1/3`} />
            <div className={`${block} mb-2 h-4 w-full`} />
            <div className={`${block} mb-2 h-4 w-full`} />
            <div className={`${block} h-4 w-3/4`} />
          </div>
        ))}
      </div>
      <div className="pt-[88px] max-lg:hidden">
        <div className={`${block} mb-3 h-3 w-28`} />
        <div className={`${block} h-8 w-16`} />
      </div>
    </main>
  );
}
