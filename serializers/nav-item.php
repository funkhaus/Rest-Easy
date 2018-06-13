<?php

    function rez_default_serialize_nav_item( $item ){

        $target_id = (int) $item->object_id;

        $permalink = $item->url;
        $relative_path = wp_make_link_relative( $permalink );

        // strip siteurl from permalink
        // useful for testing if a link is to an external resource
        $stripped_url = str_replace( get_option('siteurl'), '', $permalink );

        // Make sure we get at least an slash from the relative path
        if( ! $relative_path ){
            $relative_path = '/';
        }

        // detect if this is the main blog page
        $is_home = false;
        if ( $item->object == 'page' ) {
            $is_home = $item->object_id == get_option('page_for_posts');
        }

        // build output
        $output = array(
            'id'            => $item->ID,
            'title'         => $item->title,
            'classes'       => $item->classes,
            'permalink'     => $permalink,
            'relativePath'  => $relative_path,
            'isExternal'    => strpos($stripped_url, '/') !== 0,
            'isHome'        => $is_home,
            'parent'        => (int) $item->menu_item_parent,
            'children'      => array(),
            'postType'      => get_post_type($item),

            // included for backwards compatibility
            'is_external'   => strpos($stripped_url, '/') !== 0,
            'ID'            => $item->ID
        );

        return $output;
    }
    add_filter('rez_serialize_nav_item', 'rez_default_serialize_nav_item', 1);
