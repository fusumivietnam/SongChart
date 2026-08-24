# YouTube

Status: recommended P0 availability/embed provider.

## Approved use

- find candidate official videos;
- store video/channel external identity and canonical URL;
- render compliant IFrame embeds;
- outbound navigation to YouTube.

## Rules

- Follow current YouTube API Services Developer Policies and Required Minimum Functionality.
- Use the official IFrame Player API.
- Do not obscure player controls or branding.
- Do not autoplay by default.
- Do not download, extract, cache or transform audio/video.
- Do not present search results as exhaustive.
- Store `embeddable`/availability as expiring state.
- Prefer editor-approved associations for canonical recording-to-video links.
- Use a lightweight facade and load iframe after user interaction.
- Provide outbound fallback when embedding fails.
- Re-review policy whenever API behavior or UI changes.

Official references:
- https://developers.google.com/youtube/v3
- https://developers.google.com/youtube/iframe_api_reference
- https://developers.google.com/youtube/terms/developer-policies
- https://developers.google.com/youtube/terms/required-minimum-functionality
