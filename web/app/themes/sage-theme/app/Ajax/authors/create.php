<?php

namespace App\Ajax;

function handle_create_author(): void
{
    check_ajax_referer('book_crud_nonce', 'nonce');

    if (!is_user_logged_in() || !current_user_can('edit_posts')) {
        wp_send_json_error(['message' => 'Not allowed'], 403);
        return;
    }

    $author_name = sanitize_text_field($_POST['author_name'] ?? '');
    if (empty($author_name)) {
        wp_send_json_error(['message' => 'Author name is required']);
        return;
    }

    $post_id = wp_insert_post([
        'post_title'  => $author_name,
        'post_type'   => 'author',
        'post_status' => 'publish',
    ], true);

    if (is_wp_error($post_id)) {
        wp_send_json_error(['message' => 'Failed to create author']);
        return;
    }

    wp_send_json_success([
        'message' => 'Author created successfully',
        'author'  => [
            'id'   => $post_id,
            'name' => $author_name,
        ],
    ]);
}

add_action('wp_ajax_create_author', __NAMESPACE__ . '\\handle_create_author');
