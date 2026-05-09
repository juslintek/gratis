=== GRATIS ===
Contributors: juslintek
Requires at least: 6.5
Tested up to: 6.9
Requires PHP: 7.4
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: full-site-editing, block-patterns, custom-colors, custom-typography, dark-mode, rtl-language-support, wide-blocks, accessibility-ready, e-commerce, blog, portfolio

Everything premium themes charge for. Forever free.

== Description ==

GRATIS is a performance-first Full Site Editing (FSE) WordPress block theme. Zero upsells. Zero locked features. 100+ block patterns. MIT licensed. Better performance than anything on ThemeForest.

**Features:**
* 21 ready-to-use block patterns across 12 categories
* Full Site Editing — edit header, footer, and every template visually
* WooCommerce ready — shop, product, cart, and checkout templates included
* Live search — instant REST API powered search, zero dependencies
* Async notifications — toast system + bell icon panel
* Dark theme with full color token system via theme.json v3
* 100/100 PageSpeed out of the box
* Zero render-blocking resources
* No jQuery on frontend
* stale-while-revalidate Cache-Control headers
* HTTP/3 ready
* Accessibility: skip links, keyboard navigation, WCAG AA contrast

== Frequently Asked Questions ==

= Is GRATIS really completely free? =

Yes. GPL-2.0-or-later licensed. No "Pro" version. No upsells. Every pattern, every feature, every future update — free forever.

= Do I need Elementor or a page builder? =

No. GRATIS uses the native WordPress block editor. You get the same visual editing experience without the 400KB JS overhead.

= What PHP version is required? =

PHP 7.4 minimum, PHP 8.4+ recommended.

= Can I use this for client sites? =

Yes. GPL license means you can use it commercially, modify it, and build sites with it.

= How do I add the live search? =

Add an HTML block to any page with:
`<div class="gratis-search-wrap"><input type="search" class="gratis-search-input" placeholder="Search..."></div>`

= How do I use the notification system? =

Call from any JavaScript: `GRATIS.toast("Message", "success");` or `GRATIS.notify("Message", "info", "/url");`

== Changelog ==

= 1.0.0 =
* Initial release
* 21 block patterns across 12 categories
* WooCommerce templates (shop, product, cart, checkout)
* Live search with REST API
* Async notifications (toast + bell panel)
* Dark color scheme (styles/dark.json)
* Full Site Editing templates (index, single, page, archive, 404, search)
* 3 header variants, 2 footer variants
* Performance: 100/100 PageSpeed, zero render-blocking resources

== Resources ==

* Screenshot: Unsplash (CC0) — https://unsplash.com
* No bundled fonts — uses system-ui stack
* No bundled JavaScript libraries — vanilla JS only
* No bundled CSS frameworks — theme.json only

== Privacy ==

GRATIS does not collect any user data. No tracking. No analytics. No external calls.
The live search feature queries your own WordPress REST API only.
The notification system stores a single localStorage key (`gratis_welcomed`) to prevent repeat welcome toasts.
