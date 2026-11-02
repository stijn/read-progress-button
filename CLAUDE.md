# Working on read-progress-button

- Stijn's own plugin, all rights reserved. Keep the copyright notice in LICENSE, the plugin header and the asset files. Never add an open-source licence or third-party code under a licence that would require one (GPL); MIT, BSD or similar permissive code is fine, with its licence file kept and listed under Credits in README.md.
- The repo is private. Never commit tokens, secrets, personal email addresses or anything from wp-config.php. Commit as `102652+stijn@users.noreply.github.com`.
- It runs on aardling.com and auto-updates from GitHub releases. Releases come from `v*` tags only.
- Before tagging: bump the version in the two places in read-progress-button.php, add a CHANGELOG section and a dated roadmap.md section, and let the Test workflow pass.
- Never print, log or return the GitHub token.
- Keep it light: no jQuery, no external requests, assets on single posts only.
- The abilities may only report and install updates of this plugin.
- After a release, call `read-progress-button/self-update` through the site's connector, then `read-progress-button/plugin-status` to confirm.
