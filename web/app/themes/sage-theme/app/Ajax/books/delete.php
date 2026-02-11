<?php

namespace App\Ajax;

function handle_delete_book(): void
{
    check_ajax_referer('book_crud_nonce', 'nonce');

    if (!is_user_logged_in() || !current_user_can('delete_posts')) {
        wp_send_json_error(['message' => 'Not allowed'], 403);
        return;
    }

    $post_id = absint($_POST['id'] ?? 0);
    if (!$post_id) {
        wp_send_json_error(['message' => 'Invalid book ID']);
        return;
    }

    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'book') {
        wp_send_json_error(['message' => 'Book not found']);
        return;
    }

    $result = wp_delete_post($post_id, true);
    if (!$result) {
        wp_send_json_error(['message' => 'Failed to delete book']);
        return;
    }

    wp_send_json_success([
        'message' => 'Book deleted successfully',
        'id'      => $post_id,
    ]);
}

add_action('wp_ajax_delete_book', __NAMESPACE__ . '\\handle_delete_book');
