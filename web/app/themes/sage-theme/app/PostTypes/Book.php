<?php

/**
 * Book Custom Post Type
 *
 * This file registers a custom post type called 'book' in WordPress.
 * Custom post types allow us to create different content types beyond the default posts and pages.
 *
 * @package App\PostTypes
 */

namespace App\PostTypes;

/**
 * Register the Book custom post type
 *
 * This function is hooked into WordPress's 'init' action, which fires after WordPress has finished loading
 * but before any headers are sent. This is the perfect time to register custom post types.
 */
add_action('init', function () {
    /**
     * Define labels for the Book post type
     * These labels appear throughout the WordPress admin interface
     */
    $labels = [
        'name'                  => __('Books', 'sage'),                    // General name for the post type
        'singular_name'         => __('Book', 'sage'),                     // Name for one object of this post type
        'menu_name'             => __('Books', 'sage'),                    // Menu name in admin sidebar
        'add_new'               => __('Add New', 'sage'),                  // Text for "Add New" button
        'add_new_item'          => __('Add New Book', 'sage'),             // Text for adding a new item
        'edit_item'             => __('Edit Book', 'sage'),                // Text for editing an item
        'new_item'              => __('New Book', 'sage'),                 // Text for new item
        'view_item'             => __('View Book', 'sage'),                // Text for viewing an item
        'search_items'          => __('Search Books', 'sage'),             // Text for searching items
        'not_found'             => __('No books found', 'sage'),           // Text when no items are found
        'not_found_in_trash'    => __('No books found in Trash', 'sage'), // Text when no items in trash
        'all_items'             => __('All Books', 'sage'),                // Text for all items
    ];

    /**
     * Define arguments for the Book post type
     * These settings control how the post type behaves and what features it supports
     */
    $args = [
        'labels'              => $labels,                    // Use the labels defined above
        'public'              => true,                       // Make this post type public (visible on frontend and admin)
        'publicly_queryable'  => true,                       // Allow queries to be performed on the frontend
        'show_ui'             => true,                       // Show the admin UI for this post type
        'show_in_menu'        => true,                       // Show in the admin menu
        'query_var'           => true,                       // Enable query_var for this post type
        'rewrite'             => ['slug' => 'books'],        // URL structure: yoursite.com/books/book-title
        'capability_type'     => 'post',                     // Use the same capabilities as regular posts
        'has_archive'         => true,                       // Enable archive page (yoursite.com/books)
        'hierarchical'        => false,                      // Not hierarchical (like posts, not like pages)
        'menu_position'       => 5,                          // Position in admin menu (5 = below Posts)
        'menu_icon'           => 'dashicons-book',           // Icon in admin menu (WordPress Dashicon)
        'supports'            => ['title', 'editor', 'thumbnail'], // Features this post type supports
        'show_in_rest'        => true,                       // Enable Gutenberg editor and REST API support
    ];

    /**
     * Register the post type with WordPress
     *
     * @param string $post_type The post type key (max 20 characters, lowercase, no spaces)
     * @param array  $args      Array of arguments for the post type
     */
    register_post_type('book', $args);
});

/**
 * Register custom meta fields for Books
 *
 * Meta fields allow us to store additional information about each book.
 * These fields are stored in the wp_postmeta table in the database.
 */
add_action('init', function () {
    /**
     * Register 'author' meta field
     * This stores the book's author name
     */
    register_post_meta('book', 'author', [
        'type'         => 'string',              // Data type: string
        'single'       => true,                  // Only one value per post (not an array)
        'show_in_rest' => true,                  // Make available in REST API
        'default'      => '',                    // Default value if not set
        'description'  => 'The author of the book', // Description for documentation
    ]);

    /**
     * Register 'isbn' meta field
     * This stores the book's ISBN (International Standard Book Number)
     */
    register_post_meta('book', 'isbn', [
        'type'         => 'string',
        'single'       => true,
        'show_in_rest' => true,
        'default'      => '',
        'description'  => 'The ISBN of the book',
    ]);

    /**
     * Register 'publication_year' meta field
     * This stores the year the book was published
     */
    register_post_meta('book', 'publication_year', [
        'type'         => 'string',
        'single'       => true,
        'show_in_rest' => true,
        'default'      => '',
        'description'  => 'The publication year of the book',
    ]);

    /**
     * Register 'book_authors' meta field
     * This stores an array of author post IDs associated with the book
     */
    register_post_meta('book', 'book_authors', [
        'type'         => 'array',
        'single'       => true,
        'show_in_rest' => [
            'schema' => [
                'type'  => 'array',
                'items' => [
                    'type' => 'integer',
                ],
            ],
        ],
        'default'      => [],
        'description'  => 'Array of author post IDs associated with this book',
    ]);
});
