# Books CRUD System Documentation

This document describes the Books CRUD (Create, Read, Update, Delete) system implemented in the Sage theme.

## Overview

The Books CRUD system provides:
- **Frontend CRUD interface** for managing books at `/books-crud/` page
- **Admin interface** for managing books and authors in WordPress admin
- **Multi-author support** - books can have multiple authors
- **AJAX-powered** operations for smooth user experience
- **Custom post types** for Books and Authors

## Features

### Frontend Features
- ✅ View all books in a responsive table
- ✅ Create new books with title, authors, ISBN, publication year, and description
- ✅ Edit existing books
- ✅ Delete books with confirmation
- ✅ Multi-select dropdown for assigning multiple authors to a book
- ✅ Create new authors directly from the book form
- ✅ Real-time updates without page refresh
- ✅ Success/error message notifications
- ✅ Modal-based forms for better UX

### Admin Features
- ✅ Books menu in WordPress admin
- ✅ Authors menu in WordPress admin
- ✅ Custom meta box for selecting authors when editing books
- ✅ Standard WordPress post editing interface

## File Structure

```
web/app/themes/sage-theme/
├── app/
│   ├── Ajax/
│   │   └── BookAjax.php              # AJAX handlers for all CRUD operations
│   ├── MetaBoxes/
│   │   └── BookAuthorMetaBox.php     # Admin meta box for author selection
│   ├── PostTypes/
│   │   ├── Book.php                  # Book custom post type registration
│   │   └── Author.php                # Author custom post type registration
│   └── crud-bootstrap.php            # Loads all CRUD components
├── resources/
│   ├── js/
│   │   └── books-crud.js             # Frontend JavaScript for CRUD operations
│   └── views/
│       └── template-books-crud.blade.php  # Frontend template
```

## Custom Post Types

### Book Post Type
- **Slug**: `book`
- **Supports**: title, editor (for description)
- **Custom Fields**:
  - `isbn` - Book ISBN number
  - `publication_year` - Year of publication
  - `book_authors` - Array of author IDs (stored as serialized array)
  - `author` - Legacy text field (kept for backward compatibility)

### Author Post Type
- **Slug**: `author`
- **Supports**: title only
- **Purpose**: Stores author names as separate posts for reusability

## AJAX Endpoints

All AJAX requests go to `wp-admin/admin-ajax.php` with the following actions:

### Books
- `get_books` - Retrieve all books with their authors
- `create_book` - Create a new book
- `update_book` - Update an existing book
- `delete_book` - Delete a book

### Authors
- `get_authors` - Retrieve all authors
- `create_author` - Create a new author

### Security
All AJAX requests are protected with WordPress nonces (`book_crud_nonce`) to prevent CSRF attacks.

## Database Schema

### Books
Books are stored as WordPress posts with `post_type = 'book'`:
- `post_title` - Book title
- `post_content` - Book description
- Meta fields:
  - `isbn` - ISBN number
  - `publication_year` - Publication year
  - `book_authors` - Serialized array of author post IDs

### Authors
Authors are stored as WordPress posts with `post_type = 'author'`:
- `post_title` - Author name

## Usage

### Setting Up the Frontend Page

1. **Create a new page** in WordPress admin
2. **Set the title** (e.g., "Books Management")
3. **Select template**: Choose "Books CRUD" from the Template dropdown
4. **Publish** the page
5. **Access** the page at your site URL (e.g., `https://yoursite.com/books-crud/`)

### Managing Books (Frontend)

#### Creating a Book
1. Click "Add New Book" button
2. Fill in the form:
   - **Title** (required)
   - **Authors** - Select one or more authors from the dropdown (hold Ctrl/Cmd for multiple)
   - **ISBN** - Book's ISBN number
   - **Publication Year** - Year published
   - **Description** - Book description
3. Click "Save Book"

#### Creating a New Author
1. While in the book form, click "+ Add New Author"
2. Enter the author name
3. Click "Add Author"
4. The new author will be automatically selected in the book form

#### Editing a Book
1. Click the edit icon (pencil) next to the book
2. Modify the fields
3. Click "Save Book"

#### Deleting a Book
1. Click the delete icon (trash) next to the book
2. Confirm the deletion
3. The book will be permanently deleted

### Managing Books (Admin)

1. Go to **WordPress Admin** → **Books**
2. Use the standard WordPress interface to add/edit books
3. Use the **Book Authors** meta box to select authors
4. Publish or update the book

### Managing Authors (Admin)

1. Go to **WordPress Admin** → **Authors**
2. Add new authors like regular posts
3. The author name is the post title

## Technical Details

### JavaScript (books-crud.js)

Key functions:
- `loadAuthors()` - Fetches all authors from the server
- `loadBooks()` - Fetches all books from the server
- `displayBooks(books)` - Renders books in the table
- `openModal(bookId)` - Opens the book form modal
- `openAuthorModal()` - Opens the author creation modal
- `editBook(bookId)` - Loads book data for editing
- `deleteBook(bookId)` - Deletes a book with confirmation

### PHP (BookAjax.php)

AJAX handlers:
- `wp_ajax_get_books` - Returns all books with author details
- `wp_ajax_create_book` - Creates a new book and saves author relationships
- `wp_ajax_update_book` - Updates book and author relationships
- `wp_ajax_delete_book` - Permanently deletes a book
- `wp_ajax_get_authors` - Returns all authors
- `wp_ajax_create_author` - Creates a new author

Each handler has both `wp_ajax_` (logged-in) and `wp_ajax_nopriv_` (non-logged-in) versions.

### Blade Template (template-books-crud.blade.php)

Components:
- **Books table** - Displays all books with actions
- **Book modal** - Form for creating/editing books
- **Author modal** - Form for creating new authors
- **Message container** - Shows success/error messages

### Styling

The interface uses **Tailwind CSS** for styling:
- Responsive design
- Modern UI components
- Smooth transitions and hover effects
- Modal overlays
- Form validation styling

## Data Flow

### Creating a Book
1. User fills out the form and clicks "Save Book"
2. JavaScript collects form data including selected author IDs
3. AJAX request sent to `create_book` action
4. PHP validates data and creates the book post
5. Author IDs saved to `book_authors` meta field
6. Success response returned
7. JavaScript refreshes the books table
8. Success message displayed

### Loading Books
1. Page loads, JavaScript calls `loadAuthors()` then `loadBooks()`
2. AJAX requests sent to `get_authors` and `get_books`
3. PHP queries the database for all books and authors
4. For each book, PHP retrieves associated author details
5. Data returned as JSON
6. JavaScript renders the table with book and author information

## Customization

### Adding New Fields

To add a new field to books:

1. **Update the template** (`template-books-crud.blade.php`):
   ```blade
   <div>
     <label for="book-publisher">Publisher</label>
     <input type="text" id="book-publisher" name="publisher">
   </div>
   ```

2. **Update JavaScript** (`books-crud.js`):
   - Add field to `editBook()` function
   - Add field to table in `displayBooks()`

3. **Update PHP** (`BookAjax.php`):
   - Add sanitization in `create_book` and `update_book`
   - Add `update_post_meta()` call
   - Add field to response in `get_books`

### Styling Changes

Modify Tailwind classes in `template-books-crud.blade.php` or add custom CSS.

## Troubleshooting

### Books not loading
- Check browser console for JavaScript errors
- Verify AJAX URL is correct: `window.ajaxUrl`
- Check nonce is being generated: `window.bookNonce`
- Verify AJAX handlers are loaded in `crud-bootstrap.php`

### Authors not appearing
- Ensure Author post type is registered
- Check that authors are published (not draft)
- Verify `get_authors` AJAX handler is working

### Save not working
- Check browser console for errors
- Verify nonce is valid (regenerated every 24 hours)
- Check PHP error logs for server-side issues
- Ensure user has permission to create/edit posts

### Meta box not showing in admin
- Clear WordPress cache
- Check that `BookAuthorMetaBox.php` is loaded
- Verify you're editing a 'book' post type

## Security Considerations

- ✅ All AJAX requests use WordPress nonces
- ✅ Input sanitization with `sanitize_text_field()` and `sanitize_textarea_field()`
- ✅ Output escaping in JavaScript with `escapeHtml()`
- ✅ Post type verification before operations
- ✅ WordPress capabilities respected (user must be able to edit posts)

## Performance

- Books and authors are cached in JavaScript after initial load
- AJAX requests minimize page reloads
- Database queries use WordPress's built-in caching
- Only published posts are queried

## Future Enhancements

Potential improvements:
- [ ] Pagination for large book collections
- [ ] Search and filter functionality
- [ ] Bulk operations (delete multiple books)
- [ ] Book cover image upload
- [ ] Author biography field
- [ ] Categories/genres for books
- [ ] Export books to CSV
- [ ] Import books from CSV
- [ ] Book availability status
- [ ] User reviews and ratings

## Support

For issues or questions:
1. Check the browser console for JavaScript errors
2. Check WordPress debug log for PHP errors
3. Verify all files are in the correct locations
4. Ensure the template is selected on the page
5. Clear browser cache and WordPress cache

## Version History

- **v1.0** - Initial implementation with basic CRUD
- **v2.0** - Added multi-author support with dropdown and author creation modal
- **v2.1** - Cleaned up redundant files and improved documentation
