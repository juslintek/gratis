<?php
/**
 * Title: Timeline — Vertical
 * Slug: gratis/timeline
 * Categories: gratis-sections
 * Keywords: timeline, history, steps, process, roadmap
 */
?>
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|16","bottom":"var:preset|spacing|16"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
<!-- wp:heading {"textAlign":"center","style":{"typography":{"fontSize":"var:preset|font-size|3xl","fontWeight":"800"},"spacing":{"margin":{"bottom":"var:preset|spacing|12"}}}} --><h2 class="has-text-align-center">How it works</h2><!-- /wp:heading -->
<!-- wp:group {"style":{"spacing":{"blockGap":"0"}},"layout":{"type":"constrained","contentSize":"600px"}} -->
<div class="wp-block-group">
<?php foreach([
  ['01','Download the theme','Get GRATIS from GitHub. Zero account required. MIT licensed.'],
  ['02','Install on WordPress','Upload via Appearance → Themes or WP-CLI. Activates in seconds.'],
  ['03','Pick a pattern','Open the block editor, click +, browse 20+ patterns. One click to insert.'],
  ['04','Customize & publish','Change colors, fonts, and content in the visual editor. What you see is what visitors get.'],
] as [$num,$title,$desc]): ?>
<!-- wp:group {"style":{"spacing":{"padding":{"left":"var:preset|spacing|12"},"margin":{"bottom":"var:preset|spacing|8"}},"border":{"left":{"color":"var:preset|color|border","width":"2px","style":"solid"}}},"layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group" style="padding-left:var(--wp--preset--spacing--12);margin-bottom:var(--wp--preset--spacing--8);border-left:2px solid var(--wp--preset--color--border);position:relative">
<!-- wp:paragraph {"style":{"typography":{"fontSize":"var:preset|font-size|xs","fontWeight":"700","textTransform":"uppercase","letterSpacing":"0.15em"},"color":{"text":"var:preset|color|primary"}}} --><p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.15em;color:var(--wp--preset--color--primary)"><?= $num ?></p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"var:preset|font-size|xl","fontWeight":"700"}}} --><h3><?= $title ?></h3><!-- /wp:heading -->
<!-- wp:paragraph {"style":{"color":{"text":"var:preset|color|contrast-2"},"typography":{"fontSize":"var:preset|font-size|sm"}}} --><p style="color:var(--wp--preset--color--contrast-2);font-size:var(--wp--preset--font-size--sm)"><?= $desc ?></p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<?php endforeach; ?>
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
