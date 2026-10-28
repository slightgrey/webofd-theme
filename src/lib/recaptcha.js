import { useCallback, useEffect, useRef, useState } from "react";
import { config } from "./config.js";

// From Appearance → Web Of D → reCAPTCHA, via window.wodData.config (inc/recaptcha.php).
const recaptcha = config.recaptcha ?? { version: "v3", siteKey: "", comments: false };

// Loaded once and shared, so moving between posts doesn't add the script again.
let scriptPromise = null;

function loadScript() {
  if (!scriptPromise) {
    const src = recaptcha.version === "v3" ? `https://www.google.com/recaptcha/api.js?render=${encodeURIComponent(recaptcha.siteKey)}` : "https://www.google.com/recaptcha/api.js?render=explicit";
    scriptPromise = new Promise((resolve, reject) => {
      const script = document.createElement("script");
      script.src = src;
      script.async = true;
      script.onload = () => window.grecaptcha.ready(resolve);
      script.onerror = () => {
        scriptPromise = null;
        reject(new Error("Failed to load reCAPTCHA"));
      };
      document.head.appendChild(script);
    });
  }
  return scriptPromise;
}

/**
 * reCAPTCHA for a form when it's switched on in the settings (ported from the vincelayte theme).
 * For v2, attach `containerRef` to a div rendered while `showWidget` is true.
 */
export function useRecaptcha(form) {
  const enabled = !!recaptcha.siteKey && !!recaptcha[form];
  const showWidget = enabled && recaptcha.version === "v2";
  // v3 tokens are fetched on submit; it only needs the script loaded first.
  const isV3 = enabled && recaptcha.version === "v3";
  const [ready, setReady] = useState(!enabled);
  const containerRef = useRef(null);
  const widgetIdRef = useRef(null);

  useEffect(() => {
    if (!enabled) return;
    let cancelled = false;
    loadScript().then(
      () => !cancelled && setReady(true),
      // If the script can't load, let the reader try anyway; the server still enforces the check.
      () => !cancelled && setReady(true)
    );
    return () => {
      cancelled = true;
    };
  }, [enabled]);

  // Render the v2 checkbox once the container is in the DOM and the script is ready.
  useEffect(() => {
    if (showWidget && ready && containerRef.current && widgetIdRef.current === null && window.grecaptcha?.render) {
      widgetIdRef.current = window.grecaptcha.render(containerRef.current, { sitekey: recaptcha.siteKey, theme: "dark" });
    }
  }, [showWidget, ready]);

  const getToken = useCallback(async () => {
    if (!enabled || !window.grecaptcha) return "";
    if (isV3) return window.grecaptcha.execute(recaptcha.siteKey, { action: form });
    return window.grecaptcha.getResponse(widgetIdRef.current ?? undefined);
  }, [enabled, isV3, form]);

  // v2 tokens are single-use, so reset the checkbox after every submission attempt.
  const reset = useCallback(() => {
    if (showWidget && widgetIdRef.current !== null) window.grecaptcha.reset(widgetIdRef.current);
  }, [showWidget]);

  return { enabled, ready, showWidget, isV3, containerRef, getToken, reset };
}
