<?php
/**
 * Title: Contact — Split Layout
 * Slug: gratis/contact-split
 * Categories: gratis-contact, gratis-forms
 * Keywords: contact, form, email, get in touch
 */
?>
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|16","bottom":"var:preset|spacing|16"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
<!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|16"}}}} -->
<div class="wp-block-columns">
<!-- wp:column {"width":"40%"} -->
<div class="wp-block-column">
<!-- wp:heading {"style":{"typography":{"fontSize":"var:preset|font-size|3xl","fontWeight":"800"}}} --><h2>Get in touch</h2><!-- /wp:heading -->
<!-- wp:paragraph {"style":{"color":{"text":"var:preset|color|contrast-2"},"spacing":{"margin":{"top":"var:preset|spacing|4"}}}} --><p>Have a question or want to work together? Fill out the form and we'll get back to you within 24 hours.</p><!-- /wp:paragraph -->
<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|4","margin":{"top":"var:preset|spacing|8"}}},"layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group">
<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"}} -->
<div class="wp-block-group">
<!-- wp:paragraph {"style":{"typography":{"fontSize":"var:preset|font-size|xl"}}} --><p>📧</p><!-- /wp:paragraph -->
<!-- wp:paragraph {"style":{"typography":{"fontSize":"var:preset|font-size|sm"}}} --><p><strong>Email</strong><br><a href="mailto:hello@example.com">hello@example.com</a></p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"}} -->
<div class="wp-block-group">
<!-- wp:paragraph {"style":{"typography":{"fontSize":"var:preset|font-size|xl"}}} --><p>📍</p><!-- /wp:paragraph -->
<!-- wp:paragraph {"style":{"typography":{"fontSize":"var:preset|font-size|sm"}}} --><p><strong>Location</strong><br>Vilnius, Lithuania</p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:contact-form-7/contact-form-selector {"id":1} /-->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->
