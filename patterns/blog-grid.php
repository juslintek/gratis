<?php
/**
 * Title: Blog — Latest Posts Grid
 * Slug: gratis/blog-grid
 * Categories: gratis-blog
 * Keywords: blog, posts, grid, latest, news
 */
?>
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|16","bottom":"var:preset|spacing|16"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
<!-- wp:group {"style":{"spacing":{"margin":{"bottom":"var:preset|spacing|10"}}},"layout":{"type":"flex","justifyContent":"space-between","verticalAlignment":"center"}} -->
<div class="wp-block-group">
<!-- wp:heading {"style":{"typography":{"fontSize":"var:preset|font-size|3xl","fontWeight":"800"}}} --><h2>Latest posts</h2><!-- /wp:heading -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"className":"is-style-ghost"} --><div class="wp-block-button is-style-ghost"><a class="wp-block-button__link wp-element-button" href="/blog">View all →</a></div><!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
<!-- wp:query {"query":{"perPage":3,"postType":"post","order":"desc","orderBy":"date","inherit":false},"layout":{"type":"default"}} -->
<div class="wp-block-query">
<!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->
<!-- wp:group {"className":"is-style-card","style":{"spacing":{"padding":{"top":"0","bottom":"var:preset|spacing|6","left":"0","right":"0"}}},"layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group is-style-card">
<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9","style":{"border":{"radius":"var:custom|radius|lg var:custom|radius|lg 0 0"}}} /-->
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|5","left":"var:preset|spacing|5","right":"var:preset|spacing|5"},"blockGap":"var:preset|spacing|2"}},"layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group">
<!-- wp:post-terms {"term":"category","style":{"typography":{"fontSize":"var:preset|font-size|xs","fontWeight":"600","textTransform":"uppercase","letterSpacing":"0.08em"},"color":{"text":"var:preset|color|primary"}}} /-->
<!-- wp:post-title {"isLink":true,"style":{"typography":{"fontSize":"var:preset|font-size|lg","fontWeight":"700","lineHeight":"1.3"}}} /-->
<!-- wp:post-date {"style":{"typography":{"fontSize":"var:preset|font-size|xs"},"color":{"text":"var:preset|color|contrast-3"}}} /-->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
<!-- /wp:post-template -->
</div>
<!-- /wp:query -->
</div>
<!-- /wp:group -->
