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

        // add parent
        $parent_id = wp_get_post_parent_id($target_post->ID);
        $serialized_parent = null;

        if( $parent_id ){
            $parent = get_post($parent_id);
            $serialized_parent = apply_filters('rez_serialize_object', get_post($parent_id));
            $related['parent'] = $serialized_parent;
        }

        $related['ancestors'] = array();

        // add all ancestors
        if( $serialized_parent ){
            $related['ancestors'][] = $serialized_parent;
            while($parent_id){
                $parent_id = wp_get_post_parent_id($parent_id);
                if( $parent_id ){
                    $related['ancestors'][] = apply_filters('rez_serialize_object', get_post($parent_id));
                }
            }
        }
        // reverse to allow for easier breadcrumbs
        $related['ancestors'] = array_reverse($related['ancestors']);

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
add_filter('rez_gather_related', 'rez_default_gather_related', 1, 2);

?>
