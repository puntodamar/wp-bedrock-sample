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
 * - MetaBoxes/BookMetaBoxes.php: Meta boxes using Meta Box plugin for book fields
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
 * Load the REST API controllers
 * This sets up all the REST API endpoints for Create, Read, Update, Delete operations
 */
require_once __DIR__ . '/Api/index.php';

/**
 * Load the Book AJAX handlers (DEPRECATED - keeping for backward compatibility)
 * This sets up all the AJAX endpoints for Create, Read, Update, Delete operations
 * TODO: Remove this once all code is migrated to REST API
 */
// require_once __DIR__ . '/Ajax/index.php';

/**
 * Load the Book meta boxes using Meta Box plugin
 * This adds meta boxes for authors, ISBN, and publication year using the Meta Box plugin
 */
require_once __DIR__ . '/MetaBoxes/BookMetaBoxes.php';

