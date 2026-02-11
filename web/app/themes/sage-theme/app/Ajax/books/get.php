<?php

namespace App\Ajax;

function handle_get_books(): void
{
    check_ajax_referer('book_crud_nonce', 'nonce');

    $query = new \WP_Query([
        'post_type'      => 'book',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);

    $books = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();

            $author_ids = get_post_meta($post_id, 'book_authors', true);
            if (!is_array($author_ids)) {
                $author_ids = !empty($author_ids) ? [$author_ids] : [];
            }
            $author_ids = array_map('absint', $author_ids);

            $authors = [];
            foreach ($author_ids as $author_id) {
                $author_post = get_post($author_id);
                if ($author_post && $author_post->post_type === 'author') {
                    $authors[] = [
                        'id'   => $author_id,
                        'name' => $author_post->post_title,
                    ];
                }
            }

            // Get description from custom field, fallback to post content
            $description = get_post_meta($post_id, 'book_description', true);
            if (empty($description)) {
                $description = get_the_content();
            }

            $books[] = [
                'id'               => $post_id,
                'title'            => get_the_title(),
                'description'      => $description,
                'author'           => get_post_meta($post_id, 'author', true), // legacy
                'authors'          => $authors,
                'author_ids'       => $author_ids,
                'isbn'             => get_post_meta($post_id, 'isbn', true),
                'publication_year' => get_post_meta($post_id, 'publication_year', true),
            ];
        }
        wp_reset_postdata();
    }

    wp_send_json_success($books);
}

add_action('wp_ajax_get_books', __NAMESPACE__ . '\\handle_get_books');
add_action('wp_ajax_nopriv_get_books', __NAMESPACE__ . '\\handle_get_books');
