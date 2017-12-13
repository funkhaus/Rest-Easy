<?php

    function rez_default_serialize_nav_item( $item ){

        $target_id = (int) $item->object_id;

        $permalink = $item->url;
        $relative_path = rez_remove_siteurl( $permalink );

        // Make sure we get at least an slash from the relative path
        if( ! $relative_path ){
            $relative_path = '/';
        }

        $output = array(
            'title'         => $item->title,
            'classes'       => $item->classes,
            'permalink'     => $permalink,
            'relativePath'  => $relative_path,
            'isExternal'    => strpos($relative_path, '/') !== 0,
            'ID'            => $item->ID,
            'parent'        => (int) $item->menu_item_parent,
            'children'      => array(),

            // included for backwards compatibility
            'is_external'   => strpos($relative_path, '/') !== 0,
        );

        return $output;
    }
    add_filter('rez_serialize_nav_item', 'rez_default_serialize_nav_item', 1);
