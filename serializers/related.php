<?php

function rez_default_gather_related ($related = [], $target = null) {
    $target_post = get_post($target);

    // if it's a page...
    if ( $target_post->post_type == 'page' || $target_post->post_type == 'wps-product' ){

        // add children to related
        $args = array(
            'post_type'        => 'page',
            'orderby'          => 'menu_order',
            'posts_per_page'   => -1,
            'post_parent'      => $target_post->ID,
            'order'            => 'ASC'
        );
        $children = get_posts($args);

        $related['children'] = empty($children) ? [] : array_map(function($child){
            return apply_filters('rez_serialize_object', $child);
        }, $children);

        // add next/prev to related
        $next_id = rez_get_next_page_id($target_post);
        $prev_id = rez_get_previous_page_id($target_post);
        $related['next'] = $next_id ? apply_filters('rez_serialize_object', get_post($next_id)) : null;
        $related['prev'] = $prev_id ? apply_filters('rez_serialize_object', get_post($prev_id)) : null;
    }

    // if it's a post, add prev and next
    if ( $target_post->post_type == 'post' ){
        global $post;
        the_post();

        $prev = get_adjacent_post(false, '', true);
        $next = get_adjacent_post(false, '', false);
        $related['prev'] = $prev ? apply_filters('rez_serialize_object', $prev) : null;
        $related['next'] = $next ? apply_filters('rez_serialize_object', $next) : null;
    }

    return $related;
}
add_filter('rez_gather_related', 'rez_default_gather_related', 2);

?>
