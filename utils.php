<?php

/*
 * Next page/post ID
 */
	function rez_get_next_page_id($target_post = null) {
		$target_post = get_post($target_post);

		// set current post type
		$post_type = get_post_type( $target_post );

		// Set vars
		$current_project_id = $target_post->ID;
		$cache_key = 'all_pages_parent_'.$current_project_id;

		// Check for cached $pages
		$pages = get_transient( $cache_key );
		if ( empty( $pages ) ){
			$args = array(
				'post_type'         => $post_type,
				'order'             => 'ASC',
				'orderby'           => 'menu_order',
				'post_parent'       => $target_post->post_parent,
				'fields'            => 'ids',
				'posts_per_page'    => -1
			);
			$pages = get_posts($args);

			// Save cache
			set_transient($cache_key, $pages, 30 );
        }

		$current_key = array_search($current_project_id, $pages);
		$output = false;

		if( isset($pages[$current_key+1]) ) {
			// Next page exists
			$output = $pages[$current_key+1];
		}

		return $output;
	}

/*
 * Previous page/post ID
 */
    function rez_get_previous_page_id($target_post = null) {
        $target_post = get_post($target_post);

		// set current post type
		$post_type = get_post_type( $target_post );

		// Set vars
        $current_project_id = $target_post->ID;
        $cache_key = 'all_pages_parent_'.$current_project_id;

        // Check for cached $pages
        $pages = get_transient( $cache_key );
        if ( empty( $pages ) ){
			$args = array(
				'post_type'         => $post_type,
				'order'             => 'ASC',
				'orderby'           => 'menu_order',
				'post_parent'       => $target_post->post_parent,
				'fields'            => 'ids',
				'posts_per_page'    => -1
			);
			$pages = get_posts($args);

			// Save cache
			set_transient($cache_key, $pages, 30 );
        }

        $current_key = array_search($current_project_id, $pages);
		$output = false;

        if( isset($pages[$current_key-1]) ) {
            // Previous page exists
            $output = $pages[$current_key-1];
        }

		return $output;
    }

/*
 * Specify whether or not a page should serialize all of its attachments.
 */
	function set_attachment_serialization($target_post, $val = true) {
		$target_post = get_post($target_post);
		update_post_meta($target_post->ID, '_custom_deactivate_attachment_serialization', $val ? 'on' : '')
	}


	/*
	 * DEPRECATED FUNCTION: this used to be a custom function,
	 * but now just uses the built in WP utility function.
	 * wp_make_link_relative should now be used everywhere,
	 * this will be removed in future versions.
	 */
		function rez_remove_siteurl( $url ){
			return wp_make_link_relative($url);
		}
