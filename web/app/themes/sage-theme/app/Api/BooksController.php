<?php

namespace App\Api;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * REST API Controller for Books
 */
class BooksController
{
    /**
     * Register REST API routes
     */
    public static function register_routes(): void
    {
        \register_rest_route('sage/v1', '/books', [
            [
                'methods'             => 'GET',
                'callback'            => [self::class, 'get_books'],
                'permission_callback' => '__return_true', // Public endpoint
            ],
            [
                'methods'             => 'POST',
                'callback'            => [self::class, 'create_book'],
                'permission_callback' => [self::class, 'check_create_permission'],
                'args'                => self::get_book_schema(),
            ],
        ]);

        \register_rest_route('sage/v1', '/books/(?P<id>\d+)', [
            [
                'methods'             => 'GET',
                'callback'            => [self::class, 'get_book'],
                'permission_callback' => '__return_true',
                'args'                => [
                    'id' => [
                        'required'          => true,
                        'validate_callback' => function ($param) {
                            return is_numeric($param);
                        },
                    ],
                ],
            ],
            [
                'methods'             => 'PUT',
                'callback'            => [self::class, 'update_book'],
                'permission_callback' => [self::class, 'check_update_permission'],
                'args'                => self::get_book_schema(),
            ],
            [
                'methods'             => 'DELETE',
                'callback'            => [self::class, 'delete_book'],
                'permission_callback' => [self::class, 'check_delete_permission'],
            ],
        ]);
    }

    /**
     * Get all books
     */
    public static function get_books(WP_REST_Request $request): WP_REST_Response
    {
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
                // Exclude description from list view for better performance
                $books[] = self::prepare_book_response(get_the_ID(), false);
            }
            wp_reset_postdata();
        }

        return new WP_REST_Response($books, 200);
    }

    /**
     * Get a single book
     */
    public static function get_book(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $post_id = (int) $request['id'];
        $post    = get_post($post_id);

        if (!$post || $post->post_type !== 'book') {
            return new WP_Error('book_not_found', 'Book not found', ['status' => 404]);
        }

        // Include description in single book view
        return new WP_REST_Response(self::prepare_book_response($post_id, true), 200);
    }

    /**
     * Create a new book
     */
    public static function create_book(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $title            = sanitize_text_field($request->get_param('title') ?? '');
        $description      = sanitize_textarea_field($request->get_param('description') ?? '');
        $isbn             = sanitize_text_field($request->get_param('isbn') ?? '');
        $publication_year = sanitize_text_field($request->get_param('publication_year') ?? '');
        $author_ids       = $request->get_param('author_ids') ?? [];

        if (!is_array($author_ids)) {
            $author_ids = [];
        }
        $author_ids = array_map('absint', $author_ids);

        if (empty($title)) {
            return new WP_Error('missing_title', 'Title is required', ['status' => 400]);
        }

        $post_id = wp_insert_post([
            'post_title'   => $title,
            'post_content' => $description,
            'post_type'    => 'book',
            'post_status'  => 'publish',
        ]);

        if (is_wp_error($post_id)) {
            return new WP_Error('create_failed', 'Failed to create book', ['status' => 500]);
        }

        update_post_meta($post_id, 'isbn', $isbn);
        update_post_meta($post_id, 'publication_year', $publication_year);
        update_post_meta($post_id, 'book_authors', $author_ids);

        return new WP_REST_Response([
            'message' => 'Book created successfully',
            'book'    => self::prepare_book_response($post_id),
        ], 201);
    }

    /**
     * Update a book
     */
    public static function update_book(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $post_id = (int) $request['id'];
        $post    = get_post($post_id);

        if (!$post || $post->post_type !== 'book') {
            return new WP_Error('book_not_found', 'Book not found', ['status' => 404]);
        }

        $title            = sanitize_text_field($request->get_param('title') ?? '');
        $description      = sanitize_textarea_field($request->get_param('description') ?? '');
        $isbn             = sanitize_text_field($request->get_param('isbn') ?? '');
        $publication_year = sanitize_text_field($request->get_param('publication_year') ?? '');
        $author_ids       = $request->get_param('author_ids') ?? [];

        if (!is_array($author_ids)) {
            $author_ids = [];
        }
        $author_ids = array_map('absint', $author_ids);

        if (empty($title)) {
            return new WP_Error('missing_title', 'Title is required', ['status' => 400]);
        }

        $result = wp_update_post([
            'ID'           => $post_id,
            'post_title'   => $title,
            'post_content' => $description,
        ], true);

        if (is_wp_error($result)) {
            return new WP_Error('update_failed', 'Failed to update book', ['status' => 500]);
        }

        update_post_meta($post_id, 'isbn', $isbn);
        update_post_meta($post_id, 'publication_year', $publication_year);
        update_post_meta($post_id, 'book_authors', $author_ids);

        return new WP_REST_Response([
            'message' => 'Book updated successfully',
            'book'    => self::prepare_book_response($post_id),
        ], 200);
    }

    /**
     * Delete a book
     */
    public static function delete_book(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $post_id = (int) $request['id'];
        $post    = get_post($post_id);

        if (!$post || $post->post_type !== 'book') {
            return new WP_Error('book_not_found', 'Book not found', ['status' => 404]);
        }

        $result = wp_delete_post($post_id, true);
        if (!$result) {
            return new WP_Error('delete_failed', 'Failed to delete book', ['status' => 500]);
        }

        return new WP_REST_Response([
            'message' => 'Book deleted successfully',
            'id'      => $post_id,
        ], 200);
    }

    /**
     * Prepare book data for response
     *
     * @param int  $post_id          The book post ID
     * @param bool $include_description Whether to include the description field
     */
    private static function prepare_book_response(int $post_id, bool $include_description = true): array
    {
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

        $response = [
            'id'               => $post_id,
            'title'            => get_the_title($post_id),
            'authors'          => $authors,
            'author_ids'       => $author_ids,
            'isbn'             => get_post_meta($post_id, 'isbn', true),
            'publication_year' => get_post_meta($post_id, 'publication_year', true),
        ];

        // Only include description when requested (single book view)
        if ($include_description) {
            $description = get_post_meta($post_id, 'book_description', true);
            if (empty($description)) {
                $post = get_post($post_id);
                $description = $post->post_content ?? '';
            }
            $response['description'] = $description;
        }

        return $response;
    }

    /**
     * Get book schema for validation
     */
    private static function get_book_schema(): array
    {
        return [
            'title'            => [
                'required'          => true,
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'description'      => [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_textarea_field',
            ],
            'isbn'             => [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'publication_year' => [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'author_ids'       => [
                'type'  => 'array',
                'items' => [
                    'type' => 'integer',
                ],
            ],
        ];
    }

    /**
     * Check permission for creating books
     */
    public static function check_create_permission(): bool
    {
        return current_user_can('edit_posts');
    }

    /**
     * Check permission for updating books
     */
    public static function check_update_permission(): bool
    {
        return current_user_can('edit_posts');
    }

    /**
     * Check permission for deleting books
     */
    public static function check_delete_permission(): bool
    {
        return current_user_can('delete_posts');
    }
}
