<?php

    // Utility used on array_filter to remove $key with leading underscore ( _fails )
    function filter_leading_underscore( $key ){
        return ! preg_match('/^_/', $key);
    }

    function rez_default_serialize_post( $target_post = null ){

        $target_post = get_post( $target_post );

        if( ! $target_post ) return null;

        // Get all meta fields without leading underscore
        $meta = get_post_meta( $target_post->ID, '', true );
        $filtered_meta = array_filter( $meta, 'filter_leading_underscore', ARRAY_FILTER_USE_KEY );

        $output = array(
            'id'            => $target_post->ID,
            'title'         => get_the_title($target_post),
            'content'       => apply_filters('the_content', $target_post->post_content),
            'excerpt'       => get_the_excerpt($target_post),
            'permalink'     => get_permalink($target_post),
            'slug'          => $target_post->post_name,
            'relativePath'  => rez_remove_siteurl( $target_post ),
            'meta'          => array_map( 'reset', $filtered_meta ),
            'date'          => get_the_date('U', $target_post->ID)
        );

        return $output;
    }
    add_filter('rez_serialize_post', 'rez_default_serialize_post', 1);
