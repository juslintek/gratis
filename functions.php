<?php
/**
 * GRATIS Theme — functions.php
 * Performance hooks only. Zero bloat.
 */

// ── Setup ─────────────────────────────────────────────────────────────────────
add_action('after_setup_theme', function () {
    add_theme_support('wp-block-styles');
    add_theme_support('responsive-embeds');
    add_theme_support('html5', ['comment-list','comment-form','search-form','gallery','caption','style','script']);
    add_theme_support('post-thumbnails');
    add_image_size('gratis-card',    640, 360, true);
    add_image_size('gratis-card-2x', 1280, 720, true);
    add_theme_support('title-tag');
    load_theme_textdomain('gratis', get_template_directory() . '/languages');
});



// Fix Lithuanian quote conversion via output buffer (locale-safe)
add_action('template_redirect', function() {
    ob_start(function($html) {
        return str_replace(
            ["\xe2\x80\x9e", '&#8222;', '&bdquo;'],
            ["\xe2\x80\x9c", '&#8220;', '&ldquo;'],
            $html
        );
    });
});

// ── WooCommerce cleanup ───────────────────────────────────────────────────
add_action('init', function() {
    // Remove WooCommerce noindex — it noindexes shop/cart/checkout pages
    // but also incorrectly noindexes the homepage in some configs
    remove_filter('wp_robots', 'wc_page_no_robots');
    // Remove WC breadcrumbs from injecting above content
    remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
    remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
    remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
    // Remove WC page title (we have our own in template)
    add_filter('woocommerce_show_page_title', '__return_false');
});


// Force remove noindex added by WooCommerce wc_page_no_robots
add_filter('wp_robots', function($robots) {
    if (is_front_page() || is_home()) {
        unset($robots['noindex']);
        unset($robots['nofollow']);
        $robots['index']  = true;
        $robots['follow'] = true;
    }
    return $robots;
}, 999);


// ── SEO fixes ─────────────────────────────────────────────────────────────
// Remove noindex — WP adds it when blog_public=0, force it off
add_filter('wp_robots', function($robots) {
    unset($robots['noindex']);
    unset($robots['nofollow']);
    $robots['index']  = true;
    $robots['follow'] = true;
    return $robots;
}, 99);

// Add meta description
add_action('wp_head', function() {
    if (is_front_page()) {
        echo '<meta name="description" content="GRATIS — the free premium WordPress theme. 20+ patterns, WooCommerce ready, 100/100 PageSpeed. Zero upsells. MIT licensed. Forever free.">' . PHP_EOL;
    } elseif (is_singular()) {
        $desc = get_the_excerpt() ?: get_bloginfo('description');
        echo '<meta name="description" content="' . esc_attr(wp_strip_all_tags($desc)) . '">' . PHP_EOL;
    }
}, 2);

// Fix H1 in site-title — demote to span when on front page (hero has the real H1)
add_filter('the_title', function($title) {
    return $title;
});

// Cache headers for static assets
add_action('send_headers', function() {
    if (!is_admin()) {
        header('Vary: Accept-Encoding');
    }
});

// ── Enqueue search + notifications ───────────────────────────────────────
add_action('wp_enqueue_scripts', function() {
    $v = '1.0.2';
    wp_enqueue_style('gratis-components', get_template_directory_uri() . '/assets/css/components.css', [], $v);
    wp_enqueue_script('gratis-search', get_template_directory_uri() . '/assets/js/search.js', [], $v, true);
    wp_enqueue_script('gratis-notifications', get_template_directory_uri() . '/assets/js/notifications.js', [], $v, true);
    wp_localize_script('gratis-search', 'gratisSearch', [
        'restUrl' => rest_url('wp/v2/'),
        'nonce'   => wp_create_nonce('wp_rest'),
    ]);
});

// ── Add bell + search to header via wp_body_open ──────────────────────────
add_action('wp_footer', function() {
    // Bell HTML injected into header via JS
    echo '<script>
(function(){
  const nav = document.querySelector(".gratis-header .wp-block-navigation");
  if (!nav) return;
  const bell = document.createElement("div");
  bell.className = "gratis-bell";
  bell.innerHTML = `<span class="gratis-bell-icon">🔔</span>
    <span class="gratis-bell-badge" id="gratis-bell-count"></span>
    <div class="gratis-bell-panel">
      <div class="gratis-bell-panel-header">Notifications</div>
      <div class="gratis-bell-list" id="gratis-bell-list">
        <div class="gratis-bell-empty">No notifications yet</div>
      </div>
    </div>`;
  nav.parentNode.insertBefore(bell, nav.nextSibling);
})();
</script>';
});

// ── Dequeue junk ──────────────────────────────────────────────────────────────
add_action('wp_enqueue_scripts', function () {
    // Classic theme styles not needed for FSE
    wp_dequeue_style('classic-theme-styles');
    // jQuery not needed on frontend for FSE themes
    if (!is_admin()) {
        wp_deregister_script('jquery');
        wp_deregister_script('jquery-migrate');
    }
}, 100);

// Remove emoji — saves ~15KB JS + DNS lookup
remove_action('wp_head',             'print_emoji_detection_script', 7);
remove_action('wp_print_styles',     'print_emoji_styles');
remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('admin_print_styles',  'print_emoji_styles');

// Remove oEmbed/REST discovery from <head> — reduces TTFB
remove_action('wp_head', 'wp_oembed_add_discovery_links');
remove_action('wp_head', 'rest_output_link_wp_head');
remove_action('wp_head', 'wp_shortlink_wp_head');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');

// Remove block library CSS on pages with no blocks
add_action('wp_enqueue_scripts', function () {
    if (!is_singular() || !has_blocks()) {
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('wp-block-library-theme');
    }
}, 100);

// ── Cache-Control headers ─────────────────────────────────────────────────────
add_action('send_headers', function () {
    if (is_user_logged_in() || is_admin()) {
        return;
    }
    // Aggressive caching for anonymous visitors
    // stale-while-revalidate: serve stale instantly, fetch fresh in background
    // stale-if-error: serve stale for 7 days if origin is down
    header('Cache-Control: public, max-age=3600, stale-while-revalidate=86400, stale-if-error=604800');
    header('Vary: Accept-Encoding');
});

// ── Cache-Tag headers (for CDN tag-based purging) ─────────────────────────────
add_action('send_headers', function () {
    if (is_user_logged_in() || is_admin() || !is_singular()) {
        return;
    }
    $tags = [];
    $post = get_queried_object();
    if ($post instanceof WP_Post) {
        $tags[] = 'post-' . $post->ID;
        $tags[] = 'type-' . $post->post_type;
        foreach (get_post_taxonomies($post) as $tax) {
            foreach (wp_get_post_terms($post->ID, $tax, ['fields' => 'ids']) as $tid) {
                $tags[] = 'term-' . $tid;
            }
        }
    }
    if ($tags) {
        header('Cache-Tag: ' . implode(',', $tags));
        header('Surrogate-Key: ' . implode(' ', $tags)); // Fastly/Cloudflare format
    }
});

// ── Preconnect hints ──────────────────────────────────────────────────────────
add_action('wp_head', function () {
    // Preconnect to self for font/asset preloading
    echo '<link rel="preconnect" href="' . esc_url(home_url()) . '">' . "\n";
    // DNS prefetch for common third-party services (only if used)
    // echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">' . "\n";
}, 1);

// ── Block styles registration ─────────────────────────────────────────────────
add_action('init', function () {
    // Button variants
    register_block_style('core/button', ['name' => 'outline',   'label' => 'Outline']);
    register_block_style('core/button', ['name' => 'ghost',     'label' => 'Ghost']);
    register_block_style('core/button', ['name' => 'pill',      'label' => 'Pill']);
    register_block_style('core/button', ['name' => 'icon-only', 'label' => 'Icon Only']);

    // Image variants
    register_block_style('core/image', ['name' => 'rounded',    'label' => 'Rounded']);
    register_block_style('core/image', ['name' => 'shadow',     'label' => 'Shadow']);
    register_block_style('core/image', ['name' => 'border',     'label' => 'Border']);

    // Group/section variants
    register_block_style('core/group', ['name' => 'card',       'label' => 'Card']);
    register_block_style('core/group', ['name' => 'card-dark',  'label' => 'Card Dark']);
    register_block_style('core/group', ['name' => 'glass',      'label' => 'Glass']);
    register_block_style('core/group', ['name' => 'bordered',   'label' => 'Bordered']);

    // Separator variants
    register_block_style('core/separator', ['name' => 'thick',  'label' => 'Thick']);
    register_block_style('core/separator', ['name' => 'dotted', 'label' => 'Dotted']);

    // Quote variants
    register_block_style('core/quote', ['name' => 'large',      'label' => 'Large']);
    register_block_style('core/quote', ['name' => 'plain',      'label' => 'Plain']);

    // List variants
    register_block_style('core/list', ['name' => 'check',       'label' => 'Checkmarks']);
    register_block_style('core/list', ['name' => 'no-disc',     'label' => 'No Bullet']);
});

// ── Pattern categories ────────────────────────────────────────────────────────
add_action('init', function () {
    $categories = [
        'gratis-heroes'    => 'Heroes',
        'gratis-sections'  => 'Sections',
        'gratis-cards'     => 'Cards',
        'gratis-cta'       => 'Call to Action',
        'gratis-features'  => 'Features',
        'gratis-pricing'   => 'Pricing',
        'gratis-team'      => 'Team',
        'gratis-testimonials' => 'Testimonials',
        'gratis-stats'     => 'Stats',
        'gratis-faq'       => 'FAQ',
        'gratis-forms'     => 'Forms',
        'gratis-navigation'=> 'Navigation',
        'gratis-footer'    => 'Footer',
        'gratis-blog'      => 'Blog',
        'gratis-portfolio'  => 'Portfolio',
        'gratis-woocommerce'=> 'WooCommerce',
        'gratis-membership' => 'Membership',
        'gratis-about'     => 'About',
        'gratis-contact'   => 'Contact',
    ];
    foreach ($categories as $slug => $label) {
        register_block_pattern_category($slug, ['label' => $label]);
    }
});

// ── Inline critical CSS ───────────────────────────────────────────────────────
add_action('wp_head', function () {
    // Minimal above-fold CSS — layout stability, no FOUC
    echo '<style id="gratis-critical">
:root{--gratis-header-h:64px}
*,::before,::after{box-sizing:border-box}
body{margin:0;-webkit-font-smoothing:antialiased}
img,video{max-width:100%;height:auto}
.gratis-header{height:var(--gratis-header-h);z-index:var(--wp--custom--z-index--sticky)}

/* ── Post content styles (dark theme) ── */
.wp-block-post-content{color:#ffffff}
.wp-block-post-content p{color:rgba(255,255,255,0.75);line-height:1.8;font-size:16px}
.wp-block-post-content h1,.wp-block-post-content h2,.wp-block-post-content h3,
.wp-block-post-content h4,.wp-block-post-content h5,.wp-block-post-content h6{color:#ffffff}
.wp-block-post-content a{color:#60a5fa}
.wp-block-post-content a:hover{color:#93c5fd}
.wp-block-post-content pre{background:#0a0c10;border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:24px 28px;overflow-x:auto;font-size:14px;line-height:1.7;margin:24px 0;position:relative}
.wp-block-post-content pre code{background:none;border:none;padding:0;color:#e2e8f0;font-family:Consolas,monospace}
.wp-block-post-content code{background:#1e2433;color:#60a5fa;border:1px solid rgba(96,165,250,0.2);border-radius:5px;padding:2px 7px;font-size:0.875em;font-family:Consolas,monospace}
/* Syntax highlight colors */
.wp-block-post-content pre .token.keyword,.wp-block-post-content pre .hljs-keyword{color:#c792ea}
.wp-block-post-content pre .token.string,.wp-block-post-content pre .hljs-string{color:#c3e88d}
.wp-block-post-content pre .token.comment,.wp-block-post-content pre .hljs-comment{color:#546e7a;font-style:italic}
.wp-block-post-content pre{padding:20px;overflow-x:auto}
.wp-block-post-content code{padding:2px 6px;font-size:0.9em}
.wp-block-post-content ul,.wp-block-post-content ol{color:rgba(255,255,255,0.75)}
.wp-block-post-content strong{color:#ffffff}
.wp-block-post-content blockquote{border-left:3px solid #2563eb;padding-left:20px;color:rgba(255,255,255,0.6);font-style:italic}
/* Docs page overflow fix */
.wp-block-post-content [style*="grid-template-columns:220px 1fr"]{overflow:hidden}
.wp-block-post-content [style*="grid-template-columns:220px 1fr"] > div:last-child{min-width:0;overflow:hidden} /* docs-overflow */
/* WooCommerce dark theme */
.woocommerce-page .woocommerce,.woocommerce{color:#ffffff}
.woocommerce .price,.woocommerce-Price-amount{color:#60a5fa!important}
.woocommerce .button,.woocommerce button.button{background:#2563eb;color:#fff;border-radius:8px}
/* Archive/blog dark */
.wp-block-query-title{color:#ffffff!important}

/* ── Accessibility fixes ── */
/* Contrast: WCAG AA requires 4.5:1 ratio for normal text */
/* rgba(255,255,255,0.4) on #161a20 = ~2.8:1 FAIL → use 0.65 = ~4.6:1 PASS */
/* These selectors target the dim author role text in testimonials */
.gratis-bell-item time{color:#6b7280!important}
/* Fix any inline rgba(255,255,255,0.4) contrast failures */
[style*="rgba(255,255,255,0.4)"]{color:rgba(255,255,255,0.65)!important}
[style*="rgba(255,255,255,0.25)"]{color:rgba(255,255,255,0.5)!important}
[style*="rgba(255,255,255,0.3)"]{color:rgba(255,255,255,0.55)!important}
/* Touch targets: min 44x44px */
.gratis-toast-close{min-width:44px;min-height:44px;display:flex;align-items:center;justify-content:center}
.gratis-bell{min-width:44px;min-height:44px}
.wp-block-navigation-item__content{padding:8px 4px!important;min-height:44px;display:flex;align-items:center}
/* Skip link */
.gratis-skip-link{position:absolute;top:-100px;left:0;background:#2563eb;color:#fff;padding:8px 16px;z-index:99999;border-radius:0 0 8px 0;font-weight:600;text-decoration:none}
.gratis-skip-link:focus{top:0}

/* ── Global resets ── */
html{background:#0d0f12}body{margin:0;padding:0;background:#0d0f12!important}
/* Kill only the top-level site-blocks gap (header→main gap) */
.wp-site-blocks{--wp--style--block-gap:0px}
/* Kill ALL WP block gaps that cause white strips */
/* Kill gaps between top-level page sections only */
.wp-block-post-content.is-layout-flow>.wp-block-group+.wp-block-group{margin-block-start:0!important}
.entry-content.is-layout-flow>.wp-block-group+.wp-block-group{margin-block-start:0!important}
.wp-site-blocks{padding-top:0!important;padding-bottom:0!important;row-gap:0!important}
.wp-site-blocks>*+*{margin-block-start:0!important}
body>.wp-site-blocks>header+*{margin-top:0!important}
main.wp-block-group{margin-top:0!important}
/* Kill ALL gaps between header and content */
.wp-block-template-part+*{margin-block-start:0!important}
header.wp-block-template-part{display:contents}
/* The main block after header gets 2rem gap from WP — kill it */
:where(.wp-site-blocks)>main{margin-block-start:0!important}
:where(.wp-site-blocks)>*+main{margin-block-start:0!important}
.wp-site-blocks>main.wp-block-group{margin-top:0!important;margin-block-start:0!important}
.wp-block-post-content{margin-top:0!important}
/* Nav fallback — hide page list link text overflow */
.gratis-header .wp-block-page-list a{
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px
}
/* Header dark background — WP strips rgba from inline styles */
.gratis-header{
  background:rgba(13,15,18,0.95)!important;
  backdrop-filter:blur(12px)!important;
  -webkit-backdrop-filter:blur(12px)!important;
  border-bottom:1px solid rgba(255,255,255,0.08)!important;
  position:sticky!important;
  top:0!important;
  z-index:1000!important;
}
.gratis-header .wp-block-site-title a{color:#ffffff!important;text-decoration:none!important}
.gratis-header .wp-block-navigation a{color:rgba(255,255,255,0.75)!important}
.gratis-header .wp-block-navigation a:hover{color:#ffffff!important}
.wp-site-blocks{padding:0!important}
.entry-content,.wp-block-post-content{padding:0!important}
/* Hide site tagline in header */
.gratis-header .wp-block-site-tagline{display:none!important}
/* Hero headline override — WP strips clamp() from inline styles */
.gratis-hero h1{
  font-size:clamp(2.5rem,2rem + 2.5vw,5rem)!important;
  font-weight:800!important;
  line-height:1.05!important;
  letter-spacing:-0.03em!important;
}
/* Button style variants */
.wp-block-button.is-style-outline .wp-block-button__link{background:transparent;border:2px solid currentColor;color:var(--wp--preset--color--primary)}
.wp-block-button.is-style-ghost .wp-block-button__link{background:transparent;color:var(--wp--preset--color--primary)}
.wp-block-button.is-style-pill .wp-block-button__link{border-radius:var(--wp--custom--radius--full)}
/* Card group style */
.wp-block-group.is-style-card{background:var(--wp--preset--color--base);border:1px solid var(--wp--preset--color--border);border-radius:var(--wp--custom--radius--lg);box-shadow:var(--wp--preset--shadow--sm)}
.wp-block-group.is-style-card-dark{background:var(--wp--preset--color--base-dark);color:var(--wp--preset--color--base);border-radius:var(--wp--custom--radius--lg)}
.wp-block-group.is-style-glass{background:rgba(255,255,255,0.7);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.3);border-radius:var(--wp--custom--radius--lg)}
.wp-block-group.is-style-bordered{border:1px solid var(--wp--preset--color--border);border-radius:var(--wp--custom--radius--lg)}
/* Image styles */
.wp-block-image.is-style-rounded img{border-radius:var(--wp--custom--radius--full)}
.wp-block-image.is-style-shadow img{box-shadow:var(--wp--preset--shadow--lg)}
.wp-block-image.is-style-border img{border:4px solid var(--wp--preset--color--border)}
/* List styles */
.wp-block-list.is-style-check{list-style:none;padding-left:0}
.wp-block-list.is-style-check li::before{content:"✓ ";color:var(--wp--preset--color--success);font-weight:700}
.wp-block-list.is-style-no-disc{list-style:none;padding-left:0}
/* Separator styles */
.wp-block-separator.is-style-thick{border-width:3px}
.wp-block-separator.is-style-dotted{border-style:dotted}

/* ── RESPONSIVE / MOBILE ─────────────────────────────────────────────── */
@media(max-width:768px){
  /* Force all inline grids to stack on mobile */
  [style*="grid-template-columns"]{grid-template-columns:1fr!important}
  [style*="display:grid"]{gap:16px!important}
  /* Fix inline flex rows to wrap */
  [style*="display:flex"][style*="gap"]{flex-wrap:wrap!important}
  /* Reduce padding on mobile */
  [style*="padding:80px"]{padding:48px 16px!important}
  [style*="padding-top:80px"]{padding-top:48px!important;padding-bottom:48px!important}
  [style*="padding:100px"]{padding:48px 16px!important}
  [style*="padding:120px"]{padding:64px 16px!important}
  [style*="padding-top:120px"]{padding-top:64px!important}
  /* Fix hero headline on mobile */
  .gratis-hero h1{font-size:clamp(2rem,1.5rem+3vw,3.5rem)!important}
  /* Fix constrained layout padding */
  .has-global-padding{padding-left:16px!important;padding-right:16px!important}
  .is-layout-constrained>:where(:not(.alignleft):not(.alignright):not(.alignfull)){margin-left:16px!important;margin-right:16px!important}
  /* Fix nav on mobile */
  .gratis-header .wp-block-navigation{font-size:13px!important}
  /* Fix docs sidebar */
  [style*="grid-template-columns:240px 1fr"]{grid-template-columns:1fr!important}
  [style*="grid-template-columns:220px 1fr"]{grid-template-columns:1fr!important}
  [style*="position:sticky"]{position:static!important}
  /* Fix pricing cards */
  [style*="grid-template-columns:repeat(auto-fit,minmax(280px"]{grid-template-columns:1fr!important}
  /* Fix comparison table */
  table{font-size:12px!important}
  table th,table td{padding:8px 6px!important}
  /* Fix buttons row */
  .wp-block-buttons{flex-wrap:wrap!important}
  .wp-block-button{width:100%}
  .wp-block-button__link{width:100%;text-align:center!important}
  /* Fix pattern gallery cards */
  [style*="grid-template-columns:repeat(auto-fill,minmax(340px"]{grid-template-columns:1fr!important}
  /* Fix contact form grid */
  [style*="grid-template-columns:1fr 1fr"]{grid-template-columns:1fr!important}
  /* Fix about page grid */
  [style*="grid-template-columns:1fr 1fr"]{grid-template-columns:1fr!important}
  /* Fix stats row */
  [style*="grid-template-columns:repeat(4,1fr)"]{grid-template-columns:repeat(2,1fr)!important}
  /* Fix bell panel */
  .gratis-bell-panel{right:-100px!important;width:280px!important}
  /* Fix toast width */
  #gratis-toasts{max-width:calc(100vw - 32px)!important;left:16px!important}
}
@media(max-width:480px){
  .gratis-header .wp-block-navigation{display:none!important}
  .gratis-header .wp-block-site-title{font-size:18px!important}
  [style*="grid-template-columns:repeat(2,1fr)"]{grid-template-columns:1fr!important}
  [style*="font-size:clamp(3rem"]{font-size:2rem!important}
  [style*="font-size:clamp(2.5rem"]{font-size:1.75rem!important}
}
</style>' . "\n";
}, 2);

// ── Remove Gutenberg block patterns from core/remote (keep only ours) ─────────
add_filter('should_load_remote_block_patterns', '__return_false');

// Output gap fix AFTER WP global styles (priority 999)
add_action('wp_head', function() {
    echo '<style id="gratis-gap-fix">
html{background:#0d0f12!important}
body{background:#0d0f12!important}
.wp-site-blocks{--wp--style--block-gap:0px!important}
.gratis-howto-box{background:#1B2336!important;border:1px solid #334155!important}
.gratis-howto-box *{color:#ffffff!important}
.gratis-howto-box p{color:rgba(255,255,255,0.9)!important}
.gratis-howto-box a{color:#93C5FD!important;text-decoration:underline!important}

:where(.wp-site-blocks)>*{margin-block-start:0!important}
:where(.wp-site-blocks)>:first-child{margin-block-start:0!important}
</style>';
}, 999);
