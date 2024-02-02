<article>
	@include( 'wp.template-parts.entry-header' );
	@include( 'wp.template-parts.featured-image' );
	<div class="post-inner thin">
		<div class="entry-content">
			the_content( __( 'Continue reading', 'twentytwenty' ) );
		</div><!-- .entry-content -->
	</div><!-- .post-inner -->
	<div class="section-inner">
		@include( 'wp.template-parts.entry-author-bio' )
	</div><!-- .section-inner -->
    @include( 'wp.template-parts.navigation' );
		<div class="comments-wrapper section-inner">
		</div><!-- .comments-wrapper -->
</article><!-- .post -->