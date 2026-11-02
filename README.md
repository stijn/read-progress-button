# Read Progress Button

A round button on posts that fills as you read and leads back to the homepage. At the end of the post it turns olive green with a check mark. Plans and past releases: [roadmap.md](roadmap.md); full detail: [CHANGELOG.md](CHANGELOG.md).

Copyright (c) 2026 Stijn Vogels. All rights reserved. See [LICENSE](LICENSE).

## What it does
- On single posts, a 44 px button appears once the site header has scrolled past. Its blue ring shows how much of `.entry-content` has come into view; the cross inside links to the homepage.
- Settings > Read Progress Button: the corner it sits in, 0.75rem from both edges. Saving empties WP Super Cache when it's there.
- Matches Deluminator's switch: same size, distance from the edges, frosted disc, shadow and focus ring, light and dark. On phones it slides away and back with the switch; in the top right it sits just under it.
- One small stylesheet and one deferred script, on single posts only. No jQuery, no external requests.
- Follows Deluminator's dark mode and `prefers-reduced-motion`.

## Updates
- The plugin updates itself from the latest release of this private repo (asset `read-progress-button.zip`), through plugin-update-checker. WordPress auto-updates are on for this plugin only.
- It needs a fine-grained GitHub token: repository `stijn/read-progress-button` only, permission Contents: Read-only. Put it in wp-config.php as `define( 'READ_PROGRESS_BUTTON_GITHUB_TOKEN', '...' );` or paste it on Settings > Read Progress Button. It's never shown again, logged or returned.
- Abilities `read-progress-button/plugin-status` and `read-progress-button/self-update` report and install updates through an MCP connector.

## One-time install on a site
1. Download `read-progress-button.zip` from the latest release on GitHub.
2. Plugins > Add New Plugin > Upload Plugin, choose the zip, and replace the installed version if WordPress asks. Activate.
3. Add the token (see above). From then on it updates itself.

## Release steps
1. Change code; bump `Version:` and `READ_PROGRESS_BUTTON_VERSION` in `read-progress-button.php`; add a `## x.y.z` section to `CHANGELOG.md` and a dated `## x.y.z (date)` section to `roadmap.md` (move finished items out of "Next").
2. Commit, push to `main`; the Test workflow runs WordPress on PHP 7.4, 8.3 and 8.5.
3. Tag `vx.y.z` and push the tag. The Release workflow checks the version matches the tag, lints, builds the zip and creates the release.
4. Sites pick it up within 6 hours through WordPress's auto-updates, or right away through the self-update ability.
5. Run the Test workflow by hand (Actions > Test > Run workflow) to check the whole update cycle: an older copy updates itself to the latest release through `wp_maybe_auto_update()`.

## Credits
- [plugin-update-checker](https://github.com/YahnisElsts/plugin-update-checker) 5.6 by Jānis Elsts, MIT licence (`vendor/plugin-update-checker/license.txt`).
