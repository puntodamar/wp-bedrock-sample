# Books CRUD System - Complete Guide

This is a minimal CRUD (Create, Read, Update, Delete) sample implementation using WordPress + Bedrock + Tailwind CSS + Sage theme.

## 📁 File Structure

```
web/app/themes/sage-theme/
├── app/
│   ├── PostTypes/
│   │   └── Book.php                    # Custom post type registration
│   ├── Ajax/
│   │   └── BookAjax.php                # AJAX handlers for CRUD operations
│   └── crud-bootstrap.php              # Bootstrap file to load CRUD files
├── resources/
│   ├── views/
│   │   └── template-books-crud.blade.php  # Page template for CRUD interface
│   └── js/
│       └── books-crud.js               # JavaScript for AJAX operations
└── functions.php                       # Updated to load crud-bootstrap.php
```

## 🚀 Setup Instructions

### Step 1: Rebuild Theme Assets

Since we added a new JavaScript file (`books-crud.js`), you need to rebuild the theme assets:

```bash
# Navigate to the theme directory
cd web/app/themes/sage-theme

# Build assets for development (with hot reload)
npm run dev

# OR build for production
npm run build
```

### Step 2: Flush Rewrite Rules

After adding a custom post type, WordPress needs to regenerate its URL rewrite rules:

**Option A: Via WordPress Admin**
1. Go to WordPress Admin: `https://your-site.ddev.site/wp/wp-admin`
2. Navigate to **Settings → Permalinks**
3. Click **Save Changes** (you don't need to change anything)

**Option B: Via WP-CLI**
```bash
ddev wp rewrite flush
```

### Step 3: Create a Page for the CRUD Interface

1. Log in to WordPress Admin
2. Go to **Pages → Add New**
3. Enter a title (e.g., "Book Management")
4. On the right sidebar, find **Template** dropdown
5. Select **"Books CRUD"**
6. Click **Publish**
7. Visit the page to see your CRUD interface

## 📚 How It Works

### Custom Post Type (Book.php)

The `Book` custom post type is registered with the following fields:
- **Title**: The book's title (built-in WordPress field)
- **Description**: The book's description (built-in WordPress content field)
- **Author**: Custom meta field for the author's name
- **ISBN**: Custom meta field for the book's ISBN
- **Publication Year**: Custom meta field for the publication year

### AJAX Handlers (BookAjax.php)

Four AJAX endpoints are registered:

1. **`get_books`**: Retrieves all books from the database
   - Action: `wp_ajax_get_books` and `wp_ajax_nopriv_get_books`
   - Returns: JSON array of all books

2. **`create_book`**: Creates a new book
   - Action: `wp_ajax_create_book`
   - Required: `title`
   - Optional: `description`, `author`, `isbn`, `publication_year`
   - Returns: Success message and new book data

3. **`update_book`**: Updates an existing book
   - Action: `wp_ajax_update_book`
   - Required: `id`, `title`
   - Optional: `description`, `author`, `isbn`, `publication_year`
   - Returns: Success message and updated book data

4. **`delete_book`**: Deletes a book
   - Action: `wp_ajax_delete_book`
   - Required: `id`
   - Returns: Success message

### Frontend Template (template-books-crud.blade.php)

The Blade template provides:
- A responsive table displaying all books
- "Add New Book" button to open the modal
- Modal form for creating/editing books
- Edit and Delete buttons for each book
- Success/error message notifications

### JavaScript (books-crud.js)

The JavaScript file handles:
- Loading books via AJAX when the page loads
- Opening/closing the modal
- Form submission (create/update)
- Deleting books with confirmation
- Displaying success/error messages
- Escaping HTML to prevent XSS attacks

## 🎨 Tailwind CSS Styling

The interface uses Tailwind CSS v4 for styling:
- **Responsive design**: Works on mobile, tablet, and desktop
- **Modern UI**: Clean, professional appearance
- **Interactive elements**: Hover effects, transitions, and animations
- **Accessibility**: Proper focus states and semantic HTML

## 🔒 Security Features

1. **Nonce Verification**: All AJAX requests are verified with WordPress nonces to prevent CSRF attacks
2. **Input Sanitization**: All user input is sanitized using WordPress functions:
   - `sanitize_text_field()` for text inputs
   - `sanitize_textarea_field()` for textarea inputs
   - `intval()` for numeric inputs
3. **Output Escaping**: All output is escaped in JavaScript to prevent XSS attacks
4. **Capability Checks**: Only logged-in users can create, update, or delete books (via `wp_ajax_` hooks)

## 📖 Usage Examples

### Creating a Book

1. Click the **"+ Add New Book"** button
2. Fill in the form fields:
   - Title (required)
   - Author (optional)
   - ISBN (optional)
   - Publication Year (optional)
   - Description (optional)
3. Click **"Save Book"**
4. The book will appear in the table immediately

### Editing a Book

1. Click the **Edit** icon (pencil) next to a book
2. The modal will open with the book's current data
3. Modify any fields
4. Click **"Save Book"**
5. The table will update with the new data

### Deleting a Book

1. Click the **Delete** icon (trash) next to a book
2. Confirm the deletion in the popup dialog
3. The book will be removed from the table

## 🔧 Customization

### Adding More Fields

To add more custom fields to books:

1. **Register the meta field** in `app/PostTypes/Book.php`:
```php
register_post_meta('book', 'publisher', [
    'type'         => 'string',
    'single'       => true,
    'show_in_rest' => true,
    'default'      => '',
    'description'  => 'The publisher of the book',
]);
```

2. **Update AJAX handlers** in `app/Ajax/BookAjax.php`:
```php
// In get_books action
$books[] = [
    // ... existing fields ...
    'publisher' => get_post_meta($post_id, 'publisher', true),
];

// In create_book and update_book actions
$publisher = sanitize_text_field($_POST['publisher'] ?? '');
update_post_meta($post_id, 'publisher', $publisher);
```

3. **Add field to template** in `resources/views/template-books-crud.blade.php`:
```html
<div>
  <label for="book-publisher" class="block text-sm font-medium text-gray-700 mb-1">
    Publisher
  </label>
  <input
    type="text"
    id="book-publisher"
    name="publisher"
    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150"
    placeholder="Enter publisher name"
  >
</div>
```

4. **Update JavaScript** in `resources/js/books-crud.js`:
```javascript
// In displayBooks function, add table header
<th>Publisher</th>

// Add table cell
<td class="px-6 py-4 whitespace-nowrap">
  <div class="text-sm text-gray-600">${escapeHtml(book.publisher)}</div>
</td>

// In editBook function, populate field
document.getElementById('book-publisher').value = book.publisher;
```

### Changing Styles

All Tailwind classes can be modified in the Blade template. For example:

- Change button colors: `bg-blue-600` → `bg-green-600`
- Adjust spacing: `px-4 py-2` → `px-6 py-3`
- Modify shadows: `shadow-lg` → `shadow-xl`

## 🐛 Troubleshooting

### Books not loading

1. Check browser console for JavaScript errors
2. Verify the AJAX URL is correct: `console.log(window.ajaxUrl)`
3. Check WordPress error logs

### 404 errors on book URLs

1. Flush rewrite rules: `ddev wp rewrite flush`
2. Or go to Settings → Permalinks and click Save

### Modal not opening

1. Check browser console for JavaScript errors
2. Verify `books-crud.js` is being loaded
3. Rebuild assets: `npm run build`

### Styles not applying

1. Rebuild assets: `npm run build`
2. Clear browser cache
3. Check that Tailwind CSS is properly configured

## 📝 Code Comments

All code files include extensive inline comments explaining:
- What each function does
- How WordPress hooks work
- Security considerations
- Best practices
- Common pitfalls to avoid

This makes the code perfect for learning and understanding how WordPress CRUD operations work.

## 🎓 Learning Resources

- **WordPress Custom Post Types**: https://developer.wordpress.org/plugins/post-types/
- **WordPress AJAX**: https://developer.wordpress.org/plugins/javascript/ajax/
- **Tailwind CSS**: https://tailwindcss.com/docs
- **Blade Templates**: https://laravel.com/docs/blade
- **Fetch API**: https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API

## 📄 License

This CRUD sample is provided as-is for educational purposes.
