<?php

/**
 * Book Meta Boxes using Meta Box Plugin
 *
 * This file registers meta boxes for the Book post type using the Meta Box plugin.
 * It provides fields for authors, ISBN, and publication year with better Gutenberg integration.
 *
 * @package App\MetaBoxes
 */

namespace App\MetaBoxes;

/**
 * Check if Meta Box plugin is active and use fallback if not
 */
$use_meta_box_plugin = function_exists('rwmb_meta');

/**
 * Register meta boxes for Book post type
 *
 * Uses the Meta Box plugin's rwmb_meta_boxes filter to register fields.
 * This provides better Gutenberg integration and cleaner code.
 */
add_filter('rwmb_meta_boxes', function ($meta_boxes) {
    // Book Authors Meta Box
    $meta_boxes[] = [
        'id'         => 'book_authors',
        'title'      => __('Book Authors', 'sage'),
        'post_types' => ['book'],
        'context'    => 'side',
        'priority'   => 'default',
        'fields'     => [
            [
                'id'          => 'book_authors',
                'name'        => __('Select Authors', 'sage'),
                'type'        => 'post',
                'post_type'   => 'author',
                'field_type'  => 'checkbox_list',
                'query_args'  => [
                    'post_status'    => 'publish',
                    'posts_per_page' => -1,
                    'orderby'        => 'title',
                    'order'          => 'ASC',
                ],
                'placeholder' => __('Select one or more authors', 'sage'),
                'desc'        => __('Check one or more authors to associate with this book.', 'sage'),
            ],
        ],
    ];

    // Book Details Meta Box
    $meta_boxes[] = [
        'id'         => 'book_details',
        'title'      => __('Book Details', 'sage'),
        'post_types' => ['book'],
        'context'    => 'side',
        'priority'   => 'default',
        'fields'     => [
            [
                'id'          => 'isbn',
                'name'        => __('ISBN', 'sage'),
                'type'        => 'text',
                'placeholder' => __('Enter ISBN', 'sage'),
                'desc'        => __('International Standard Book Number', 'sage'),
            ],
            [
                'id'          => 'publication_year',
                'name'        => __('Publication Year', 'sage'),
                'type'        => 'text',
                'placeholder' => __('e.g., 2024', 'sage'),
                'desc'        => __('Year the book was published', 'sage'),
            ],
        ],
    ];

    // Book Description Meta Box with WYSIWYG Editor
    $meta_boxes[] = [
        'id'         => 'book_description',
        'title'      => __('Book Description', 'sage'),
        'post_types' => ['book'],
        'context'    => 'normal',
        'priority'   => 'high',
        'fields'     => [
            [
                'id'      => 'book_description',
                'name'    => __('Description', 'sage'),
                'type'    => 'wysiwyg',
                'raw'     => false,
                'options' => [
                    'textarea_rows' => 10,
                    'teeny'         => false,
                    'media_buttons' => true,
                    'quicktags'     => true,
                ],
            ],
        ],
    ];

    return $meta_boxes;
});

/**
 * Fallback: Native WordPress meta boxes if Meta Box plugin is not active
 */
if (!function_exists('rwmb_meta')) {
    /**
     * Add native WordPress meta boxes
     */
    add_action('add_meta_boxes', function () {
        // Authors meta box
        add_meta_box(
            'book_authors_meta_box',
            __('Book Authors', 'sage'),
            'App\MetaBoxes\render_book_authors_meta_box',
            'book',
            'side',
            'default'
        );

        // Book Details meta box
        add_meta_box(
            'book_details_meta_box',
            __('Book Details', 'sage'),
            'App\MetaBoxes\render_book_details_meta_box',
            'book',
            'side',
            'default'
        );

        // Book Description meta box with WYSIWYG
        add_meta_box(
            'book_description_meta_box',
            __('Book Description', 'sage'),
            'App\MetaBoxes\render_book_description_meta_box',
            'book',
            'normal',
            'high'
        );
    });

    /**
     * Render the Author Selection meta box
     */
    function render_book_authors_meta_box($post) {
        wp_nonce_field('book_authors_meta_box', 'book_authors_meta_box_nonce');

        $selected_authors = get_post_meta($post->ID, 'book_authors', true);
        if (!is_array($selected_authors)) {
            $selected_authors = [];
        }

        $authors = get_posts([
            'post_type'      => 'author',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'post_status'    => 'publish',
        ]);

        if (empty($authors)) {
            echo '<p>' . __('No authors found. Please create authors first.', 'sage') . '</p>';
            echo '<p><a href="' . admin_url('post-new.php?post_type=author') . '" class="button">' . __('Add New Author', 'sage') . '</a></p>';
            return;
        }

        echo '<div style="max-height: 300px; overflow-y: auto;">';
        foreach ($authors as $author) {
            $checked = in_array($author->ID, $selected_authors) ? 'checked="checked"' : '';
            echo '<label style="display: block; margin-bottom: 8px;">';
            echo '<input type="checkbox" name="book_author_ids[]" value="' . esc_attr($author->ID) . '" ' . $checked . '> ';
            echo esc_html($author->post_title);
            echo '</label>';
        }
        echo '</div>';
    }

    /**
     * Render the Book Details meta box
     */
    function render_book_details_meta_box($post) {
        wp_nonce_field('book_details_meta_box', 'book_details_meta_box_nonce');

        $isbn = get_post_meta($post->ID, 'isbn', true);
        $publication_year = get_post_meta($post->ID, 'publication_year', true);

        echo '<div style="margin-bottom: 15px;">';
        echo '<label for="book_isbn" style="display: block; margin-bottom: 5px;"><strong>' . __('ISBN:', 'sage') . '</strong></label>';
        echo '<input type="text" id="book_isbn" name="book_isbn" value="' . esc_attr($isbn) . '" style="width: 100%;" placeholder="' . esc_attr__('Enter ISBN', 'sage') . '">';
        echo '</div>';

        echo '<div>';
        echo '<label for="book_publication_year" style="display: block; margin-bottom: 5px;"><strong>' . __('Publication Year:', 'sage') . '</strong></label>';
        echo '<input type="text" id="book_publication_year" name="book_publication_year" value="' . esc_attr($publication_year) . '" style="width: 100%;" placeholder="' . esc_attr__('Enter year (e.g., 2024)', 'sage') . '">';
        echo '</div>';
    }

    /**
     * Render the Book Description meta box with WYSIWYG editor
     */
    function render_book_description_meta_box($post) {
        wp_nonce_field('book_description_meta_box', 'book_description_meta_box_nonce');

        $description = get_post_meta($post->ID, 'book_description', true);

        // Use wp_editor with specific settings for Gutenberg compatibility
        $editor_id = 'book_description_editor';
        $settings = [
            'textarea_name' => 'book_description',
            'textarea_rows' => 10,
            'media_buttons' => true,
            'teeny'         => false,
            'quicktags'     => true,
            'wpautop'       => true,
            'tinymce'       => [
                'toolbar1' => 'formatselect,bold,italic,underline,strikethrough,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,wp_more,spellchecker,fullscreen,wp_adv',
                'toolbar2' => 'styleselect,formatselect,forecolor,backcolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
            ],
        ];

        wp_editor($description, $editor_id, $settings);
    }

    /**
     * Save all book meta fields (consolidated handler)
     */
    add_action('save_post_book', function ($post_id) {
        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save book authors
        if (isset($_POST['book_authors_meta_box_nonce']) &&
            wp_verify_nonce($_POST['book_authors_meta_box_nonce'], 'book_authors_meta_box')) {
            $author_ids = isset($_POST['book_author_ids']) ? array_map('intval', $_POST['book_author_ids']) : [];
            update_post_meta($post_id, 'book_authors', $author_ids);
        }

        // Save book details (ISBN and publication year)
        if (isset($_POST['book_details_meta_box_nonce']) &&
            wp_verify_nonce($_POST['book_details_meta_box_nonce'], 'book_details_meta_box')) {
            if (isset($_POST['book_isbn'])) {
                update_post_meta($post_id, 'isbn', sanitize_text_field($_POST['book_isbn']));
            }
            if (isset($_POST['book_publication_year'])) {
                update_post_meta($post_id, 'publication_year', sanitize_text_field($_POST['book_publication_year']));
            }
        }

        // Save book description
        if (isset($_POST['book_description_meta_box_nonce']) &&
            wp_verify_nonce($_POST['book_description_meta_box_nonce'], 'book_description_meta_box')) {
            if (isset($_POST['book_description'])) {
                update_post_meta($post_id, 'book_description', wp_kses_post($_POST['book_description']));
            } else {
                // If field is empty, save empty string
                update_post_meta($post_id, 'book_description', '');
            }
        }
    }, 10, 1);
}

/**
 * Display custom columns in the admin books list
 */
add_filter('manage_book_posts_columns', function ($columns) {
    // Add new columns after the title
    $new_columns = [];
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'title') {
            $new_columns['book_authors'] = __('Authors', 'sage');
            $new_columns['isbn'] = __('ISBN', 'sage');
            $new_columns['publication_year'] = __('Publication Year', 'sage');
        }
    }
    return $new_columns;
});

/**
 * Populate the custom columns with data
 */
add_action('manage_book_posts_custom_column', function ($column, $post_id) {
    if ($column === 'book_authors') {
        // Get the value using get_post_meta (Meta Box stores data as regular post meta)
        $author_ids = get_post_meta($post_id, 'book_authors', true);

        if (empty($author_ids) || !is_array($author_ids)) {
            echo '<em>' . __('No authors', 'sage') . '</em>';
            return;
        }

        $author_names = [];
        foreach ($author_ids as $author_id) {
            $author = get_post($author_id);
            if ($author) {
                $author_names[] = esc_html($author->post_title);
            }
        }

        echo !empty($author_names) ? implode(', ', $author_names) : '<em>' . __('No authors', 'sage') . '</em>';
    }

    if ($column === 'isbn') {
        $isbn = get_post_meta($post_id, 'isbn', true);
        echo !empty($isbn) ? esc_html($isbn) : '<em>' . __('N/A', 'sage') . '</em>';
    }

    if ($column === 'publication_year') {
        $year = get_post_meta($post_id, 'publication_year', true);
        echo !empty($year) ? esc_html($year) : '<em>' . __('N/A', 'sage') . '</em>';
    }
}, 10, 2);

/**
 * Make the custom columns sortable
 */
add_filter('manage_edit-book_sortable_columns', function ($columns) {
    $columns['book_authors'] = 'book_authors';
    $columns['isbn'] = 'isbn';
    $columns['publication_year'] = 'publication_year';
    return $columns;
});
