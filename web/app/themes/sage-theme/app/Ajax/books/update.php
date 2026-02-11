<?php

namespace App\Ajax;

function handle_update_book(): void
{
    check_ajax_referer('book_crud_nonce', 'nonce');

    if (!is_user_logged_in() || !current_user_can('edit_posts')) {
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

    $title            = sanitize_text_field($_POST['title'] ?? '');
    $description      = sanitize_textarea_field($_POST['description'] ?? '');
    $author           = sanitize_text_field($_POST['author'] ?? '');
    $isbn             = sanitize_text_field($_POST['isbn'] ?? '');
    $publication_year = sanitize_text_field($_POST['publication_year'] ?? '');

    $author_ids = isset($_POST['author_ids']) && is_array($_POST['author_ids'])
        ? array_map('absint', $_POST['author_ids'])
        : [];

    if (empty($title)) {
        wp_send_json_error(['message' => 'Title is required']);
        return;
    }

    $result = wp_update_post([
        'ID'           => $post_id,
        'post_title'   => $title,
        'post_content' => $description,
    ], true);

    if (is_wp_error($result)) {
        wp_send_json_error(['message' => 'Failed to update book']);
        return;
    }

    update_post_meta($post_id, 'author', $author);
    update_post_meta($post_id, 'isbn', $isbn);
    update_post_meta($post_id, 'publication_year', $publication_year);
    update_post_meta($post_id, 'book_authors', $author_ids);

    wp_send_json_success([
        'message' => 'Book updated successfully',
        'book'    => [
            'id'               => $post_id,
            'title'            => $title,
            'description'      => $description,
            'author'           => $author,
            'isbn'             => $isbn,
            'publication_year' => $publication_year,
            'author_ids'       => $author_ids,
        ],
    ]);
}

add_action('wp_ajax_update_book', __NAMESPACE__ . '\\handle_update_book');
