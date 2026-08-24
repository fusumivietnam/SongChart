# Cloudflare Turnstile

Status: recommended.

Rules:
- server-side Siteverify is mandatory;
- validate within token lifetime;
- tokens are single-use;
- use action/hostname checks where available;
- use official test credentials;
- combine with Laravel rate limiting;
- do not add to low-risk flows without evidence;
- handle script/CSP failure accessibly.
