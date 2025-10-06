<?php
/**
 * Load all shortcode files from the shortcodes directory.
 */
add_action('after_setup_theme', function () {
    $dir = __DIR__ . '/shortcodes';
    if (is_dir($dir)) {
        foreach (glob($dir . '/*.php') as $file) {
            require_once $file;
        }
    }
});
