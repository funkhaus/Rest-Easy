<?php

function rez_default_gather_related ($object) {
    $output = [];

    // if we have a featured image, add it
    if ( $thumbnail_id = get_post_thumbnail_id( $object->ID ) ){
        $output['featured_attachment'] = apply_filters('rez_serialize_object', get_post($thumbnail_id));
    }

    // if it's a page...
    if ( $object->post_type == 'page' ){

        // add children to related
        $args = array(
            'post_type'        => 'page',
            'orderby'          => 'menu_order',
            'posts_per_page'   => -1,
            'post_parent'      => $object->ID,
            'order'            => 'ASC'
        );
        $children = get_posts($args);
        $output['children'] = empty($children) ? [] : array_map(function($child){
            return apply_filters('rez_serialize_object', $child);
        }, $children);

        // add next/prev to related
        $next_id = rez_get_next_page_id($object);
        $prev_id = rez_get_previous_page_id($object);
        $output['next'] = $next_id ? apply_filters('rez_serialize_object', get_post($next_id)) : null;
        $output['prev'] = $prev_id ? apply_filters('rez_serialize_object', get_post($prev_id)) : null;
    }

    // if it's a post, add prev and next
    if ( $object->post_type == 'post' ){
        global $post;
        the_post();
        $prev = get_adjacent_post(false, '', true);
        $next = get_adjacent_post(false, '', false);
        $output['prev'] = $prev ? apply_filters('rez_serialize_object', $prev) : null;
        $output['next'] = $next ? apply_filters('rez_serialize_object', $next) : null;
    }

    return $output;
}
add_filter('rez_gather_related', 'rez_default_gather_related', 1);


?>
