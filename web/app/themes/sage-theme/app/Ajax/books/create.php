<?php

namespace App\Ajax;

function handle_create_book(): void
{
    check_ajax_referer('book_crud_nonce', 'nonce');

    // Optional (recommended): restrict create to logged-in users with capability
    if (!is_user_logged_in() || !current_user_can('edit_posts')) {
        wp_send_json_error(['message' => 'Not allowed'], 403);
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

    $post_id = wp_insert_post([
        'post_title'   => $title,
        'post_content' => $description,
        'post_type'    => 'book',
        'post_status'  => 'publish',
    ]);

    if (is_wp_error($post_id)) {
        wp_send_json_error(['message' => 'Failed to create book']);
        return;
    }

    update_post_meta($post_id, 'author', $author);
    update_post_meta($post_id, 'isbn', $isbn);
    update_post_meta($post_id, 'publication_year', $publication_year);
    update_post_meta($post_id, 'book_authors', $author_ids);

    wp_send_json_success([
        'message' => 'Book created successfully',
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

add_action('wp_ajax_create_book', __NAMESPACE__ . '\\handle_create_book');
