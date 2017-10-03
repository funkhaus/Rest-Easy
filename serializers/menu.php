<?php

    function rez_default_serialize_menu( $menu_object = null ){

        if( $menu_object === null ){
            return $output;
        }

        $nav_items = wp_get_nav_menu_items($menu_object->name);

        // Format menu items
        $formatted_items = array_map( function($nav_item){
            return apply_filters('rez_serialize_object', $nav_item);
        }, $nav_items );

        $output = array(
            'name'          => $menu_object->name,
            'slug'          => $menu_object->slug,
            'items'         => $formatted_items
        );

        return $output;
    }

    add_filter('rez_serialize_menu', 'rez_default_serialize_menu', 1);
