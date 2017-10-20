<?php

    function rez_default_serialize_menu( $menu_object = null ){

        if( $menu_object === null ){
            return $output;
        }

        $nav_items = wp_get_nav_menu_items($menu_object->name);

        $output = array(
            'name'          => $menu_object->name,
            'slug'          => $menu_object->slug,
            'items'         => build_tree($nav_items)
        );

        return $output;
    }

    add_filter('rez_serialize_menu', 'rez_default_serialize_menu', 1);

    // building tree from flat array - https://stackoverflow.com/a/28429487/3856675
    // modified slightly to match serialization
    function build_tree(array &$elements, $parentId = 0) {

        $branch = array();

        // for each element in this set...
        foreach ($elements as &$element) {

            // ...first, apply the rez_serialize_object filter
            $filtered_element = apply_filters('rez_serialize_object', $element);

            // if we're at the top level...
            if ((int)$element->menu_item_parent == $parentId) {

                // ...build the tree of this menu item's children...
                $children = build_tree($elements, $element->ID);
                // ...then apply the appropriate filter and save under the correct menu item
                if ($children) {
                    foreach( $children as $child ){
                        $filtered_element['children'][] = $child;
                    }
                }

                // save the top level
                $branch[] = $filtered_element;
                // destroy the original element
                unset($element);
            }
        }

        return $branch;
    }
