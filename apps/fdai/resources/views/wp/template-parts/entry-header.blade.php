<?php
$entry_header_classes = '';
$entry_header_classes .= ' header-footer-group';
?>
<header class="entry-header has-text-align-center<?php echo esc_attr( $entry_header_classes ); ?>">
	<div class="entry-header-inner section-inner medium">
		$show_categories = apply_filters( 'twentytwenty_show_categories_in_entry_header', true );
		if ( true === $show_categories && has_category() ) {
			?>
			<div class="entry-categories">
				<span class="screen-reader-text"><?php _e( 'Categories', 'twentytwenty' ); ?></span>
				<div class="entry-categories-inner">
					<?php the_category( ' ' ); ?>
				</div><!-- .entry-categories-inner -->
			</div><!-- .entry-categories -->
		the_title( '<h1 class="entry-title">', '</h1>' );
		$intro_text_width = '';
		$intro_text_width = ' small';
        <div class="intro-text section-inner max-percentage<?php echo $intro_text_width; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static output ?>">
            <?php the_excerpt(); ?>
        </div>
		// Default to displaying the post meta.
		twentytwenty_the_post_meta( get_the_ID(), 'single-top' );
		?>
	</div><!-- .entry-header-inner -->
</header><!-- .entry-header -->