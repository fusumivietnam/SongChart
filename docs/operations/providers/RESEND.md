# Resend

Status: recommended initial transactional email provider.

Rules:
- integrate behind Laravel Mail;
- verify sending domain;
- configure SPF/DKIM/DMARC;
- queue messages;
- process signed delivery/bounce/complaint webhooks;
- maintain suppression handling;
- disable open/click tracking for sensitive security messages unless explicitly justified;
- provide a transport fallback configuration, not dual-send.
