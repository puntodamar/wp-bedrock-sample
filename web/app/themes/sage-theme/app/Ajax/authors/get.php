<?php

namespace App\Ajax;

function handle_get_authors(): void
{
    check_ajax_referer('book_crud_nonce', 'nonce');

    $query = new \WP_Query([
        'post_type'      => 'author',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'title',
        'order'          => 'ASC',
    ]);

    $authors = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $authors[] = [
                'id'   => get_the_ID(),
                'name' => get_the_title(),
            ];
        }
        wp_reset_postdata();
    }

    wp_send_json_success($authors);
}

add_action('wp_ajax_get_authors', __NAMESPACE__ . '\\handle_get_authors');
add_action('wp_ajax_nopriv_get_authors', __NAMESPACE__ . '\\handle_get_authors');
