<?php

/*
 * Primary function to gather and serializer
 * data for any given page
 */
    function rez_build_all_data() {

        // build out data
        $output = array(
            'site'      => rez_build_site_data(),
            'meta'      => rez_build_meta_data(),
            'loop'      => rez_build_loop_data()
        );

		return apply_filters('rez_build_all_data', $output);
    }

/*
 * Build out data that goes on every page
 */
    function rez_build_site_data() {

        // Get all available menus (https://paulund.co.uk/get-all-wordpress-navigation-menus)
        $menus = get_terms( 'nav_menu', array( 'hide_empty' => true ) );

        // build out data
        $output = array(
            'themeUrl'      => get_template_directory_uri(),
            'name'     		=> get_bloginfo('name'),
			'description'	=> get_bloginfo('description'),
			'menus'         => array_map(function($menu){
                return apply_filters('rez_serialize_object', $menu);
            }, $menus)
        );

        return apply_filters('rez_build_site_data', $output);
    }

/*
 * Build out meta info for this page
 */
    function rez_build_meta_data($queried_object) {
        global $wp;
        $permalink = home_url(add_query_arg(array(), $wp->request));

        $output = array(
            'self'          => $permalink,
            'is404'         => is_404()
        );

        return apply_filters('rez_build_meta_data', $output);
    }

/*
 * Build out meta info for this page
 */
    function rez_build_loop_data() {
		global $wp_query;

		// map over queried posts
        $output = array_map(function($target_post){

			// gather related serialized items for this post
			$related = apply_filters('rez_gather_related', $target_post);

			// run post through main serializer
			$serialized = apply_filters('rez_serialize_object', $target_post);

			return array_merge($serialized, array('related' => $related));

		}, $wp_query->posts);

        wp_reset_postdata();

		return apply_filters('rez_build_loop_data', $output);
    }
