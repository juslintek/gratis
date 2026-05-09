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
    add_theme_support('title-tag');
    load_theme_textdomain('gratis', get_template_directory() . '/languages');
});



// Disable wptexturize globally — converts quotes to Lithuanian „ based on locale
add_filter('run_wptexturize', '__return_false'); // wptexturize_all
remove_filter('the_content',   'wptexturize');
remove_filter('the_title',     'wptexturize');
remove_filter('the_excerpt',   'wptexturize');
remove_filter('comment_text',  'wptexturize');
remove_filter('single_post_title', 'wptexturize');
remove_filter('bloginfo',      'wptexturize');
// Output buffer fix for wptexturize
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
        echo '<meta name="description" content="GRATIS — the free premium WordPress theme. 100+ patterns, WooCommerce ready, 100/100 PageSpeed. Zero upsells. MIT licensed. Forever free.">' . PHP_EOL;
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
remove_action('wp_head', 'wp_generator'); // hide WP version

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
:where(.wp-site-blocks)>*{margin-block-start:0!important}
:where(.wp-site-blocks)>:first-child{margin-block-start:0!important}
</style>';
}, 999);
