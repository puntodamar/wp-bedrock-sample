<?php

/**
 * Book AJAX Handlers
 *
 * This file contains all AJAX handlers for CRUD operations on Books.
 * AJAX (Asynchronous JavaScript and XML) allows us to update parts of a web page
 * without reloading the entire page, creating a smoother user experience.
 *
 * WordPress AJAX works by sending requests to wp-admin/admin-ajax.php
 * with an 'action' parameter that determines which function to call.
 *
 * @package App\Ajax
 */

namespace App\Ajax;

/**
 * Get all books (READ operation)
 *
 * This function retrieves all books from the database and returns them as JSON.
 * It's called when the frontend needs to display the list of books.
 *
 * Hook explanation:
 * - wp_ajax_{action} = for logged-in users
 * - wp_ajax_nopriv_{action} = for non-logged-in users
 */
add_action('wp_ajax_get_books', function () {
    /**
     * Verify nonce for security
     * Nonces are security tokens that help prevent CSRF (Cross-Site Request Forgery) attacks.
     * They ensure the request is coming from your site and not from a malicious source.
     */
    check_ajax_referer('book_crud_nonce', 'nonce');

    /**
     * Query books from database
     * WP_Query is WordPress's class for querying posts from the database.
     */
    $query = new \WP_Query([
        'post_type'      => 'book',           // Only get posts of type 'book'
        'posts_per_page' => -1,               // Get all books (no pagination)
        'post_status'    => 'publish',        // Only get published books
        'orderby'        => 'date',           // Order by publication date
        'order'          => 'DESC',           // Newest first
    ]);

    /**
     * Prepare books data for JSON response
     * We loop through each book and extract the data we need
     */
    $books = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post(); // Set up post data for template tags

            $post_id = get_the_ID(); // Get the current post ID

            /**
             * Build an array with all book information
             * get_post_meta() retrieves custom field values from the database
             * The third parameter 'true' means return a single value (not an array)
             */
            $books[] = [
                'id'               => $post_id,
                'title'            => get_the_title(),                              // Book title
                'description'      => get_the_content(),                            // Book description
                'author'           => get_post_meta($post_id, 'author', true),      // Custom field: author
                'isbn'             => get_post_meta($post_id, 'isbn', true),        // Custom field: ISBN
                'publication_year' => get_post_meta($post_id, 'publication_year', true), // Custom field: year
            ];
        }
        wp_reset_postdata(); // Reset post data to avoid conflicts
    }

    /**
     * Send JSON response back to the browser
     * wp_send_json_success() automatically sets the correct headers and encodes the data as JSON
     */
    wp_send_json_success($books);
});

// Also allow non-logged-in users to get books
add_action('wp_ajax_nopriv_get_books', function () {
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

            $books[] = [
                'id'               => $post_id,
                'title'            => get_the_title(),
                'description'      => get_the_content(),
                'author'           => get_post_meta($post_id, 'author', true),
                'isbn'             => get_post_meta($post_id, 'isbn', true),
                'publication_year' => get_post_meta($post_id, 'publication_year', true),
            ];
        }
        wp_reset_postdata();
    }

    wp_send_json_success($books);
});

/**
 * Create a new book (CREATE operation)
 *
 * This function creates a new book post in the database with the provided data.
 */
add_action('wp_ajax_create_book', function () {
    // Verify nonce for security
    check_ajax_referer('book_crud_nonce', 'nonce');

    /**
     * Sanitize input data
     * NEVER trust user input! Always sanitize and validate.
     * sanitize_text_field() removes unwanted characters and makes the input safe.
     */
    $title            = sanitize_text_field($_POST['title'] ?? '');
    $description      = sanitize_textarea_field($_POST['description'] ?? '');
    $author           = sanitize_text_field($_POST['author'] ?? '');
    $isbn             = sanitize_text_field($_POST['isbn'] ?? '');
    $publication_year = sanitize_text_field($_POST['publication_year'] ?? '');

    /**
     * Validate required fields
     * Make sure the user provided a title
     */
    if (empty($title)) {
        wp_send_json_error(['message' => 'Title is required']);
        return;
    }

    /**
     * Create the post
     * wp_insert_post() creates a new post in the database
     */
    $post_id = wp_insert_post([
        'post_title'   => $title,           // Book title
        'post_content' => $description,     // Book description
        'post_type'    => 'book',           // Post type
        'post_status'  => 'publish',        // Publish immediately
    ]);

    /**
     * Check if post creation was successful
     * wp_insert_post() returns the post ID on success, or 0/WP_Error on failure
     */
    if (is_wp_error($post_id)) {
        wp_send_json_error(['message' => 'Failed to create book']);
        return;
    }

    /**
     * Save custom meta fields
     * update_post_meta() saves custom field values to the database
     * If the meta key doesn't exist, it creates it. If it exists, it updates it.
     */
    update_post_meta($post_id, 'author', $author);
    update_post_meta($post_id, 'isbn', $isbn);
    update_post_meta($post_id, 'publication_year', $publication_year);

    /**
     * Return success response with the new book data
     */
    wp_send_json_success([
        'message' => 'Book created successfully',
        'book'    => [
            'id'               => $post_id,
            'title'            => $title,
            'description'      => $description,
            'author'           => $author,
            'isbn'             => $isbn,
            'publication_year' => $publication_year,
        ],
    ]);
});

/**
 * Update an existing book (UPDATE operation)
 *
 * This function updates an existing book post with new data.
 */
add_action('wp_ajax_update_book', function () {
    // Verify nonce for security
    check_ajax_referer('book_crud_nonce', 'nonce');

    /**
     * Get and validate the book ID
     * intval() converts the value to an integer, preventing SQL injection
     */
    $post_id = intval($_POST['id'] ?? 0);

    if (!$post_id) {
        wp_send_json_error(['message' => 'Invalid book ID']);
        return;
    }

    /**
     * Check if the post exists and is a book
     */
    $post = get_post($post_id);

    if (!$post || $post->post_type !== 'book') {
        wp_send_json_error(['message' => 'Book not found']);
        return;
    }

    /**
     * Sanitize input data
     */
    $title            = sanitize_text_field($_POST['title'] ?? '');
    $description      = sanitize_textarea_field($_POST['description'] ?? '');
    $author           = sanitize_text_field($_POST['author'] ?? '');
    $isbn             = sanitize_text_field($_POST['isbn'] ?? '');
    $publication_year = sanitize_text_field($_POST['publication_year'] ?? '');

    /**
     * Validate required fields
     */
    if (empty($title)) {
        wp_send_json_error(['message' => 'Title is required']);
        return;
    }

    /**
     * Update the post
     * wp_update_post() updates an existing post in the database
     */
    $result = wp_update_post([
        'ID'           => $post_id,         // Which post to update
        'post_title'   => $title,           // New title
        'post_content' => $description,     // New description
    ]);

    /**
     * Check if update was successful
     */
    if (is_wp_error($result)) {
        wp_send_json_error(['message' => 'Failed to update book']);
        return;
    }

    /**
     * Update custom meta fields
     */
    update_post_meta($post_id, 'author', $author);
    update_post_meta($post_id, 'isbn', $isbn);
    update_post_meta($post_id, 'publication_year', $publication_year);

    /**
     * Return success response with updated book data
     */
    wp_send_json_success([
        'message' => 'Book updated successfully',
        'book'    => [
            'id'               => $post_id,
            'title'            => $title,
            'description'      => $description,
            'author'           => $author,
            'isbn'             => $isbn,
            'publication_year' => $publication_year,
        ],
    ]);
});

/**
 * Delete a book (DELETE operation)
 *
 * This function deletes a book post from the database.
 */
add_action('wp_ajax_delete_book', function () {
    // Verify nonce for security
    check_ajax_referer('book_crud_nonce', 'nonce');

    /**
     * Get and validate the book ID
     */
    $post_id = intval($_POST['id'] ?? 0);

    if (!$post_id) {
        wp_send_json_error(['message' => 'Invalid book ID']);
        return;
    }

    /**
     * Check if the post exists and is a book
     */
    $post = get_post($post_id);

    if (!$post || $post->post_type !== 'book') {
        wp_send_json_error(['message' => 'Book not found']);
        return;
    }

    /**
     * Delete the post
     * wp_delete_post() permanently deletes a post from the database
     * The second parameter 'true' means bypass trash and delete permanently
     * Set to 'false' to move to trash instead
     */
    $result = wp_delete_post($post_id, true);

    /**
     * Check if deletion was successful
     */
    if (!$result) {
        wp_send_json_error(['message' => 'Failed to delete book']);
        return;
    }

    /**
     * Return success response
     */
    wp_send_json_success([
        'message' => 'Book deleted successfully',
        'id'      => $post_id,
    ]);
});
