<?php
    function rez_default_serialize_attachment( $attachment = null ){
        $output = array();
        $attachment = get_post($attachment);

        if ( !$attachment ) return null;

        // set unique key for transient
        $key = 'rez_serializer_att_' . $attachment->ID;

        // only do the work if transient does not exist
        if ( ! $output = get_transient($key) ) {

            // get all sizes
            $sizes = array_merge(get_intermediate_image_sizes(), array('full'));

            // save alt text
            $alt_text = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true);
            if(empty($alt_text)) {
                // Try the caption if no alt text
                $alt_text = trim(strip_tags( $attachment->post_excerpt ));
            }
            if(empty($alt_text)) {
                // Try the title if no caption
                $alt_text = trim(strip_tags( $attachment->post_title ));
            }

            // Get all meta fields without leading underscore
            $meta = get_post_meta( $attachment->ID, '', true );
            $filtered_meta = array_filter( $meta, 'filter_leading_underscore', ARRAY_FILTER_USE_KEY );

            // Save attachment data
            $output['ID'] = $attachment->ID;
            $output['title'] = get_the_title($attachment->ID);
            $output['alt'] = $alt_text;
            $output['caption'] = trim(strip_tags( $attachment->post_excerpt ));
            $output['description'] = trim(strip_tags( $attachment->post_content ));
            $output['meta'] = array_map(function($i){ return $i[0]; }, $filtered_meta);

            // add image colors if FIC (https://github.com/funkhaus/funky-colors) is installed
            if ( function_exists('get_primary_image_color') ){
                $output['primaryColor'] = get_primary_image_color($attachment->ID);

                // included for backwards compatibility
                $output['primary_color'] = get_primary_image_color($attachment->ID);
            }

            if ( function_exists('get_second_image_color') ){
                $output['secondaryColor'] = get_second_image_color($attachment->ID);

                // included for backwards compatibility
                $output['secondary_color'] = get_second_image_color($attachment->ID);
            }

            // build out sizes
            foreach ( $sizes as $size ){
                $img_data = wp_get_attachment_image_src($attachment->ID, $size);

                $output['sizes'][$size] = array(
                    'url'       => $img_data[0],
                    'width'     => $img_data[1],
                    'height'    => $img_data[2],
                    'html'      => wp_get_attachment_image($attachment->ID, $size),
                );
            }

            // Add post type
            $output['postType'] = get_post_type($attachment);

            // save as transient
            set_transient($key, $output, 15);
        }

        return $output;
    }
    add_filter('rez_serialize_attachment', 'rez_default_serialize_attachment', 1);
