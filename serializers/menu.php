<?php

    function rez_default_serialize_menu( $menu_name = null ){
        $fetched = wp_get_nav_menu_items( $menu_name );

        // Exit if menu not found
        if( ! $fetched ) return false;

        // Format menu items
        $formatted_items = array_map( function($nav_item){
            return apply_filters('rez_serialize_object', $nav_item);
        }, $fetched );

        // Save menu object
        $menu_object = wp_get_nav_menu_object( $menu_name );

        $output = array(
            'name'          => $menu_object->name,
            'slug'          => $menu_object->slug,
            'items'         => $formatted_items
        );

        return $output;
    }

    add_filter('rez_serialize_menu', 'rez_default_serialize_menu', 1);
