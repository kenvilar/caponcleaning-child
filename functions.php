<?php

/**
 * Load all shortcode files from the shortcodes directory.
 */
add_action( 'after_setup_theme', function () {
	$dir = __DIR__ . '/shortcodes';
	if ( is_dir( $dir ) ) {
		foreach ( glob( $dir . '/*.php' ) as $file ) {
			require_once $file;
		}
	}
} );

// Allow SVG
add_filter( 'wp_check_filetype_and_ext', function ( $data, $file, $filename, $mimes ) {
	global $wp_version;
	if ( $wp_version !== '4.7.1' ) {
		return $data;
	}

	$filetype = wp_check_filetype( $filename, $mimes );

	return [
		'ext'             => $filetype['ext'],
		'type'            => $filetype['type'],
		'proper_filename' => $data['proper_filename']
	];
}, 10, 4 );
function cc_mime_types( $mimes ) {
	$mimes['svg'] = 'image/svg+xml';

	return $mimes;
}

add_filter( 'upload_mimes', 'cc_mime_types' );