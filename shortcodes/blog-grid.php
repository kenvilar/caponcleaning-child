<?php
/**
 * Shortcode: [blog_cards]
 * Outputs recent blog posts in a Tailwind card grid with search and pagination.
 * No attributes. Uses query args within the same page:
 * - searchTerm: search term
 * - blog_page: page number
 */

if ( ! function_exists( 'ccc_blog_cards_shortcode' ) ) {
	function ccc_blog_cards_shortcode() {
		$per_page = 9; // 3 columns x 2 rows
		$current  = isset( $_GET['blog_page'] ) ? max( 1, (int) $_GET['blog_page'] ) : 1;
		$search   = isset( $_GET['searchTerm'] ) ? sanitize_text_field( wp_unslash( $_GET['searchTerm'] ) ) : '';

		$args = array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'posts_per_page'      => $per_page,
			'paged'               => $current,
		);
		if ( $search !== '' ) {
			$args['s'] = $search;
		}

		$q = new WP_Query( $args );

		ob_start();
		?>
		<div class="w-full">
			<!-- Search bar -->
			<form method="get" action="" class="w-full flex justify-center mb-[20px]!">
				<div class="relative w-full max-w-[360px]! rounded-full border border-slate-200 shadow-sm overflow-hidden">
					<input type="text" name="searchTerm" value="<?php
					echo esc_attr( $search ); ?>" placeholder="Search the blog"
					       class="w-full pl-[12px]! pr-[4px]! py-[10px]! rounded-full border border-slate-200 shadow-sm focus:outline-none text-slate-700"/>
					<button type="submit" aria-label="Search"
					        style="color: #90a1b9;height: 100%;position: absolute;right: 0;left: auto;top: auto;bottom: auto;background-color: white;font-size: initial;border: 0px solid transparent;padding: 0 4px;border-radius: 50%;overflow: hidden;">
						<svg xmlns="http://www.w3.org/2000/svg" class="h-[20px]! w-[20px]!" viewBox="0 0 20 20" fill="currentColor">
							<path fill-rule="evenodd"
							      d="M12.9 14.32a8 8 0 111.414-1.414l3.387 3.386a1 1 0 01-1.414 1.415l-3.387-3.387zM14 8a6 6 0 11-12 0 6 6 0 0112 0z"
							      clip-rule="evenodd"/>
						</svg>
					</button>
					<?php
					// Preserve other query args except our own pagination param
					foreach ( $_GET as $key => $value ) {
						if ( $key !== 'searchTerm' && $key !== 'blog_page' ) {
							echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '">';
						}
					}
					?>
				</div>
			</form>
			<?php
			if ( $q->have_posts() ) : ?>
				<div class="grid grid-cols-3 sm:grid-cols-1 md:grid-cols-2 gap-32">
					<?php
					while ( $q->have_posts() ) : $q->the_post(); ?>
						<article class="bg-white rounded-lg shadow-md overflow-hidden border border-slate-100 group">
							<a href="<?php
							the_permalink(); ?>">
								<div class="h-190! overflow-hidden">
									<?php
									if ( has_post_thumbnail() ) : ?>
										<?php
										the_post_thumbnail( 'large',
											array( 'class' => 'w-full object-cover group-hover:scale-105 transition-transform duration-300' ) ); ?>
									<?php
									else : ?>
										<div class="w-full h-full bg-slate-100 flex items-center justify-center text-slate-400">No image
										</div>
									<?php
									endif; ?>
								</div>
								<div class="px-12 h-full flex flex-col justify-between">
									<h3 class="text-[18px]! pb-16 font-semibold leading-tight text-slate-900 mb-3" my-text-limit="2">
										<?php
										the_title(); ?>
									</h3>
									<p class="text-slate-600 text-sm h-full" my-text-limit="4">
										<?php
										echo esc_html( wp_trim_words( get_the_excerpt() ?: wp_strip_all_tags( get_the_content() ),
											30,
											'...' ) ); ?></p>
								</div>
							</a>
						</article>
					<?php
					endwhile;
					wp_reset_postdata(); ?>
				</div>
				<?php
				// Simple prev/next pagination
				$total_pages   = (int) $q->max_num_pages;
				$base_url      = remove_query_arg( array( 'blog_page' ) );
				$args_preserve = array();
				if ( $search !== '' ) {
					$args_preserve['blog_s'] = $search;
				}

				$prev_url = $current > 1 ? add_query_arg( array_merge( $args_preserve, array( 'blog_page' => $current - 1 ) ),
					$base_url ) : '';
				$next_url = $current < $total_pages ? add_query_arg( array_merge( $args_preserve,
					array( 'blog_page' => $current + 1 ) ),
					$base_url ) : '';
				?>
				<div class="flex items-center justify-center gap-12 mt-[20px]">
					<?php
					if ( $prev_url ): ?>
						<a href="<?php
						echo esc_url( $prev_url ); ?>"
						   class="px-8 py-2 text-center rounded-md border border-slate-200 text-slate-700 hover:bg-slate-50">
							Previous
						</a>
					<?php
					else: ?>
						<span class="px-8 py-2 text-center rounded-md border border-slate-200 text-slate-400 cursor-not-allowed">Previous</span>
					<?php
					endif; ?>
					<span class="text-sm text-slate-500">Page <?php
						echo (int) $current; ?> of <?php
						echo (int) max( 1, $total_pages ); ?></span>
					<?php
					if ( $next_url ): ?>
						<a href="<?php
						echo esc_url( $next_url ); ?>"
						   class="px-8 py-2 text-center rounded-md border border-slate-200 text-slate-700 hover:bg-slate-50">Next
						</a>
					<?php
					else: ?>
						<span class="px-8 py-2 text-center rounded-md border border-slate-200 text-slate-400 cursor-not-allowed">Next</span>
					<?php
					endif; ?>
				</div>
			<?php
			else : ?>
				<p class="text-center text-slate-500">No posts found.</p>
			<?php
			endif; ?>
		</div>
		<script>
			document.addEventListener("DOMContentLoaded", function () {
				var elementsWithTextLimit = document.querySelectorAll('[my-text-limit]');
				elementsWithTextLimit.forEach(function (element) {
					var numberOfLines = parseInt(element.getAttribute('my-text-limit'));
					if (!isNaN(numberOfLines)) {
						element.style.overflow = 'hidden';
						element.style.textOverflow = 'ellipsis';
						element.style.display = '-webkit-box';
						element.style.webkitLineClamp = numberOfLines;
						element.style.webkitBoxOrient = 'vertical';
					}
				});
			});
		</script>
		<?php

		return ob_get_clean();
	}

	add_shortcode( 'blog_cards', 'ccc_blog_cards_shortcode' );
}
