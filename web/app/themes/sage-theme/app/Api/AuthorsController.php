<?php

namespace App\Api;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * REST API Controller for Authors
 */
class AuthorsController
{
    /**
     * Register REST API routes
     */
    public static function register_routes(): void
    {
        \register_rest_route('sage/v1', '/authors', [
            [
                'methods'             => 'GET',
                'callback'            => [self::class, 'get_authors'],
                'permission_callback' => '__return_true', // Public endpoint
            ],
            [
                'methods'             => 'POST',
                'callback'            => [self::class, 'create_author'],
                'permission_callback' => [self::class, 'check_create_permission'],
                'args'                => [
                    'name' => [
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ],
        ]);

        \register_rest_route('sage/v1', '/authors/(?P<id>\d+)', [
            [
                'methods'             => 'GET',
                'callback'            => [self::class, 'get_author'],
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
        ]);
    }

    /**
     * Get all authors
     */
    public static function get_authors(WP_REST_Request $request): WP_REST_Response
    {
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

        return new WP_REST_Response($authors, 200);
    }

    /**
     * Get a single author
     */
    public static function get_author(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $post_id = (int) $request['id'];
        $post    = get_post($post_id);

        if (!$post || $post->post_type !== 'author') {
            return new WP_Error('author_not_found', 'Author not found', ['status' => 404]);
        }

        return new WP_REST_Response([
            'id'   => $post_id,
            'name' => get_the_title($post_id),
        ], 200);
    }

    /**
     * Create a new author
     */
    public static function create_author(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $author_name = sanitize_text_field($request->get_param('name') ?? '');

        if (empty($author_name)) {
            return new WP_Error('missing_name', 'Author name is required', ['status' => 400]);
        }

        $post_id = wp_insert_post([
            'post_title'  => $author_name,
            'post_type'   => 'author',
            'post_status' => 'publish',
        ], true);

        if (is_wp_error($post_id)) {
            return new WP_Error('create_failed', 'Failed to create author', ['status' => 500]);
        }

        return new WP_REST_Response([
            'message' => 'Author created successfully',
            'author'  => [
                'id'   => $post_id,
                'name' => $author_name,
            ],
        ], 201);
    }

    /**
     * Check permission for creating authors
     */
    public static function check_create_permission(): bool
    {
        return current_user_can('edit_posts');
    }
}
