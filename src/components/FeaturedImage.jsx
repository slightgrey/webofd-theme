// Featured image at the top of a post or page: article width, 16:9 crop, card corners.
export default function FeaturedImage({ image }) {
  if (!image) return null;

  return (
    <figure className="m-0 mb-10 overflow-hidden rounded-md bg-surface shadow-sm">
      <img
        src={image.src}
        srcSet={image.srcset || undefined}
        sizes="(min-width: 860px) 780px, calc(100vw - 32px)"
        width={image.width}
        height={image.height}
        alt={image.alt}
        fetchPriority="high"
        decoding="async"
        className="block aspect-[16/9] h-auto w-full object-cover"
      />
    </figure>
  );
}
