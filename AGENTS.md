# Repository guidance

GRATIS is the public WordPress Full Site Editing theme in this repository. It is separate from the private Gratis suite. Keep the theme compatible with the WordPress and PHP versions declared in `style.css`, use WordPress APIs and existing files, and do not add dependencies without a concrete need. Theme structure lives in `templates/`, `parts/`, `patterns/`, `styles/`, and `theme.json`; PHP hooks live in `functions.php`.

Preserve WordPress security boundaries: escape rendered values, sanitize and validate untrusted input, and use nonces and capability checks for state-changing actions. Keep authenticated and admin responses out of public caching. Retain the `gratis` text domain for translatable strings.

There is no checked-in automated test or lint configuration. For PHP changes, run `php -l` on each changed PHP file; for template, style, or behavior changes, inspect the affected theme in WordPress when a local install is available. Do not report checks that were not run.
