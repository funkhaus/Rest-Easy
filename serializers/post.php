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

        setup_postdata($target_post);
        $excerpt = get_the_excerpt($target_post->ID);
        if ( !$excerpt ) {
            $content = get_post_field('post_content', $target_post->ID);
            $trimmed = apply_filters('wp_trim_excerpt', $content);
            $stripped = strip_shortcodes($trimmed);
            $excerpt = apply_filters('the_excerpt', $stripped);
        }

        $output = array(
            'id'            => $target_post->ID,
            'title'         => get_the_title($target_post),
            'content'       => apply_filters('the_content', $target_post->post_content),
            'excerpt'       => $excerpt,
            'permalink'     => get_permalink($target_post),
            'slug'          => $target_post->post_name,
            'relativePath'  => rez_remove_siteurl( $target_post ),
            'meta'          => array_map( 'reset', $filtered_meta ),
            'date'          => get_the_date('U', $target_post->ID)
        );

        // if we have a featured image, add it
        if ( $thumbnail_id = get_post_thumbnail_id( $target_post->ID ) ){
            $output['featured_attachment'] = apply_filters('rez_serialize_object', get_post($thumbnail_id));
        }

        // Add all attached media
        $attached_media = get_attached_media('image', $target_post->ID);
        $output['attachedMedia'] = array();
        foreach( $attached_media as $single_attached_media ){
            $output['attachedMedia'][] = apply_filters('rez_serialize_object', $single_attached_media);
        }

        return $output;
    }
    add_filter('rez_serialize_post', 'rez_default_serialize_post', 1);
