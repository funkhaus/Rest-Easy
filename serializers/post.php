<?php

    // Utility used on array_filter to remove $key with leading underscore ( _fails )
    function filter_leading_underscore( $key ){
        return ! preg_match('/^_/', $key);
    }

    function rez_default_serialize_post( $target_post = null ){
        $target_post = get_post( $target_post );
        if( ! $target_post ) return null;

        // set unique key for transient
        $key = 'rez_serializer_post_' . $target_post->ID;

        // only do the work if transient does not exist
        if ( ! $output = get_transient($key) ) {

            // Get all meta fields without leading underscore
            $meta = get_post_meta( $target_post->ID, '', true );
            $filtered_meta = array_filter( $meta, 'filter_leading_underscore', ARRAY_FILTER_USE_KEY );

            setup_postdata($target_post);
            $excerpt = get_the_excerpt($target_post->ID);
            if ( !$excerpt ) {
                $content = get_post_field('post_content', $target_post->ID);
                $trimmed = apply_filters('wp_trim_excerpt', $content);
                $excerpt = apply_filters('the_excerpt', $trimmed);
            }

            $output = array(
                'id'            => $target_post->ID,
                'title'         => get_the_title($target_post),
                'content'       => apply_filters('the_content', $target_post->post_content),
                'excerpt'       => $excerpt,
                'permalink'     => get_permalink($target_post),
                'slug'          => $target_post->post_name,
                'relativePath'  => wp_make_link_relative( get_permalink($target_post) ),
                'meta'          => array_map( 'reset', $filtered_meta ),
                'date'          => get_the_date('U', $target_post->ID),
                'isFront'       => get_option('page_on_front') == $target_post->ID,
                'isBlog'        => get_option('page_for_posts') == $target_post->ID,
                'isCategory'    => is_category()
            );

            // if we have a featured image, add it
            if ( $thumbnail_id = get_post_thumbnail_id( $target_post->ID ) ){
                $output['featuredAttachment'] = apply_filters('rez_serialize_object', get_post($thumbnail_id));
            }

            // Add all attached media
            $attached_media = get_attached_media('image', $target_post->ID);
            $output['attachedMedia'] = array();
            foreach( $attached_media as $single_attached_media ){
                $output['attachedMedia'][] = apply_filters('rez_serialize_object', $single_attached_media);
            }

            // Add terms
            $taxonomy_names = get_post_taxonomies($target_post->ID);
            foreach($taxonomy_names as $taxonomy_name) {
                $terms = wp_get_post_terms( $target_post->ID, $taxonomy_name);
                if( !empty($terms) ) {
                    $output['terms'][$taxonomy_name] = $terms;
                }
            }

            // Add post type
            $output['postType'] = get_post_type($target_post);

            // save transient
            set_transient($key, $output, 15);
        }

        return $output;
    }
    add_filter('rez_serialize_post', 'rez_default_serialize_post', 1);
