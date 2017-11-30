<?php
    function rez_default_serialize_attachment( $attachment = null ){
        if ( !$attachment ) return null;

        $output = array();

        $attachment = get_post($attachment);
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

        // Save attachment data
        $output['ID'] = $attachment->ID;
        $output['title'] = get_the_title($attachment->ID);
        $output['alt'] = $alt_text;
        $output['caption'] = trim(strip_tags( $attachment->post_excerpt ));
        $output['description'] = trim(strip_tags( $attachment->post_content ));

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


        return $output;
    }
    add_filter('rez_serialize_attachment', 'rez_default_serialize_attachment', 1);
