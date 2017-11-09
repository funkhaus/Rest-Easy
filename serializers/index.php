<?php

    // load serializers
    include_once re_pd() . 'serializers/related.php';
    include_once re_pd() . 'serializers/attachment.php';
    include_once re_pd() . 'serializers/nav-item.php';
    include_once re_pd() . 'serializers/post.php';
    include_once re_pd() . 'serializers/menu.php';

    function rez_default_serialize_object($object){
        $output = null;

        if ( $object->post_type == 'attachment' ){
            $output = apply_filters('rez_serialize_attachment', $object);
        } else if ( $object->taxonomy == 'nav_menu' ){
            $output = apply_filters('rez_serialize_menu', $object);
        } else if ( $object->post_type == 'nav_menu_item' ) {
            $output = apply_filters('rez_serialize_nav_item', $object);
        } else if ( $object->post_type == 'wps-product' ) {
            // Special serializer for wps-product post types - see https://github.com/funkhaus/wp-shopify
            $output = apply_filters('rez_serialize_post', $object);
            $output['productId'] = $object->_wshop_product_id;
        } else {
            $output = apply_filters('rez_serialize_post', $object);
        }

        return $output;
    }
    add_filter('rez_serialize_object', 'rez_default_serialize_object', 1);
