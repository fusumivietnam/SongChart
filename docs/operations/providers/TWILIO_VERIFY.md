# Twilio Verify

Status: deferred/gated.

Potential use:
- SMS or WhatsApp phone verification;
- risk-based step-up;
- managed OTP channel selection;
- future passkey/device flows.

Rules:
- add only when phone possession has product value;
- evaluate per-country delivery, regulations and fraud cost;
- apply spend caps and rate limits;
- normalize phone numbers;
- never expose whether an account exists;
- never log verification codes;
- preserve TOTP/recovery alternatives for administrators.
