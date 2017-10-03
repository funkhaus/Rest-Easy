<?php

    function rez_default_serialize_nav_item( $item ){

        $target_id = (int) $item->object_id;

        $relative_path = rez_remove_siteurl( $target_id );

        // Make sure we get at least an slash from the relative path
        if( ! $relative_path ){
            $relative_path = '/';
        }

        $output = array(
            'title'         => $item->title,
            'classes'       => $item->classes,
            'permalink'     => $item->url,
            'relativePath'  => $relative_path,
            'is_external'   => $item->type_label == 'Custom Link',
            'ID'            => $item->ID,
            'parent'        => (int)$item->menu_item_parent
        );

        return $output;
    }
    add_filter('rez_serialize_nav_item', 'rez_default_serialize_nav_item', 1);
