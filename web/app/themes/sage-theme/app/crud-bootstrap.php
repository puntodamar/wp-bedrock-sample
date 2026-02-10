<?php

/**
 * CRUD Bootstrap File
 *
 * This file loads all the custom functionality for the Books CRUD system.
 * It's included in functions.php to ensure all our custom code is loaded.
 *
 * Files loaded:
 * - PostTypes/Book.php: Registers the Book custom post type
 * - PostTypes/Author.php: Registers the Author custom post type
 * - Ajax/BookAjax.php: Handles all AJAX requests for CRUD operations
 * - MetaBoxes/BookAuthorMetaBox.php: Custom meta box for author selection in admin
 *
 * @package App
 */

/**
 * Load the Book custom post type
 * This registers the 'book' post type in WordPress
 */
require_once __DIR__ . '/PostTypes/Book.php';

/**
 * Load the Author custom post type
 * This registers the 'author' post type in WordPress
 */
require_once __DIR__ . '/PostTypes/Author.php';

/**
 * Load the Book AJAX handlers
 * This sets up all the AJAX endpoints for Create, Read, Update, Delete operations
 */
require_once __DIR__ . '/Ajax/BookAjax.php';

/**
 * Load the Book-Author meta box
 * This adds a custom meta box for selecting authors when editing books in the admin
 */
require_once __DIR__ . '/MetaBoxes/BookAuthorMetaBox.php';

