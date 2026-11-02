# Changelog

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
