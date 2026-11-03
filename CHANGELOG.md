# Changelog

## 1.1.1
- The repo is public now, so updates no longer need a GitHub token. The token field stays, optional: it raises GitHub's limit of 60 requests an hour per server.
- The self-update ability no longer refuses to run without a token.
- The update-cycle test runs without a token.

## 1.1.0
- Looks and sits like Deluminator's switch: 44 px instead of 58, 0.75rem from the edges instead of 50 px, the same frosted disc with a hairline shadow instead of a solid one with a heavy drop shadow, the same dark disc and focus ring in dark mode, a 24 px icon and a thinner ring.
- On phones it slides out of the way with Deluminator's switch while you read on, and comes back with it when you scroll up. At the end of the post the check mark stays.
- It appears once the site header has scrolled past, so it never sits on the site title.
- Top right: just under Deluminator's switch. Bottom corners: above a menu fixed to the bottom of the screen. Top corners clear the admin bar.

## 1.0.0
- First release from GitHub, and the first that updates itself: from the latest release of the private repo stijn/read-progress-button, with WordPress auto-updates on for this plugin only. Needs a read-only GitHub token (wp-config.php or Settings > Read Progress Button).
- Settings > Read Progress Button: choose the corner (top left, top right, bottom left, bottom right). Saving empties WP Super Cache.
- The ring is an SVG circle instead of a canvas, so it stays sharp on high-density screens; it's drawn once per frame while scrolling.
- No jQuery: the script is small, deferred and on single posts only, like the stylesheet.
- The link to the homepage has a label for screen readers; the button can't be clicked while it's hidden; motion stops for readers who ask for less; colours follow Deluminator's dark mode.
- Abilities to report and install its own updates, for an MCP connector.
- Copyright (c) 2026 Stijn Vogels. All rights reserved.

## 0.1
- The round progress button with a close link to the homepage, installed by hand on aardling.com.
