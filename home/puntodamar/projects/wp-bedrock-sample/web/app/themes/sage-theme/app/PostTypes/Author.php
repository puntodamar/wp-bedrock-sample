<?php

/**
 * Author Custom Post Type
 */

namespace App\PostTypes;

add_action('init', function () {
    $labels = [
        'name'                  => __('Authors', 'sage'),
        'singular_name'         => __('Author', 'sage'),
        'menu_name'             => __('Authors', 'sage'),
        'add_new'               => __('Add New', 'sage'),
        'add_new_item'          => __('Add New Author', 'sage'),
        'edit_item'             => __('Edit Author', 'sage'),
        'new_item'              => __('New Author', 'sage'),
        'view_item'             => __('View Author', 'sage'),
        'search_items'          => __('Search Authors', 'sage'),
        'not_found'             => __('No authors found', 'sage'),
        'not_found_in_trash'    => __('No authors found in Trash', 'sage'),
        'all_items'             => __('All Authors', 'sage'),
    ];

    $args = [
        'labels'              => $labels,
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => true,
        'rewrite'             => ['slug' => 'authors'],
        'capability_type'     => 'post',
        'has_archive'         => true,
        'hierarchical'        => false,
        'menu_position'       => 6,
        'menu_icon'           => 'dashicons-admin-users',
        'supports'            => ['title', 'editor', 'thumbnail'],
        'show_in_rest'        => true,
    ];

    register_post_type('author', $args);
});
