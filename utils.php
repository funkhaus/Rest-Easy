<?php

/*
 * Removes site url to retrieve relative path
 */
	function rez_remove_siteurl( $url ){
		$permalink = is_string($url) ? $url : get_permalink($url);
		$replaced = str_replace( get_option('siteurl'), '', $permalink );
		return rtrim( $replaced, '/' );
	}

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
