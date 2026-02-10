# Books CRUD - Quick Start Guide

## 🚀 3-Step Setup

### 1. Build Theme Assets
```bash
cd web/app/themes/sage-theme
npm run build
```

### 2. Flush Rewrite Rules
```bash
ddev wp rewrite flush
```

### 3. Create CRUD Page
1. Go to WordPress Admin: `https://your-site.ddev.site/wp/wp-admin`
2. **Pages → Add New**
3. Title: "Book Management"
4. Template: Select **"Books CRUD"**
5. Click **Publish**
6. Visit the page!

## 📋 What You Get

✅ **Create** new books with title, author, ISBN, year, and description
✅ **Read** all books in a beautiful table
✅ **Update** existing books with inline editing
✅ **Delete** books with confirmation
✅ **Responsive** design with Tailwind CSS
✅ **Secure** with WordPress nonces and sanitization
✅ **Well-commented** code for learning

## 🎯 Key Files

| File | Purpose |
|------|---------|
| `app/PostTypes/Book.php` | Registers the Book custom post type |
| `app/Ajax/BookAjax.php` | Handles all CRUD AJAX requests |
| `resources/views/template-books-crud.blade.php` | The CRUD interface template |
| `resources/js/books-crud.js` | JavaScript for AJAX operations |

## 💡 Quick Tips

- **All code is heavily commented** - perfect for learning!
- **Tailwind CSS v4** is already configured
- **No jQuery** - uses vanilla JavaScript and Fetch API
- **Security built-in** - nonces, sanitization, and escaping
- **Extensible** - easy to add more fields or features

## 📖 Need More Details?

See `CRUD_README.md` for complete documentation including:
- How it works
- Security features
- Customization guide
- Troubleshooting
- Code examples

## 🎨 Customization Example

Want to change the primary color from blue to purple?

In `template-books-crud.blade.php`, replace:
- `bg-blue-600` → `bg-purple-600`
- `hover:bg-blue-700` → `hover:bg-purple-700`
- `text-blue-600` → `text-purple-600`
- `ring-blue-500` → `ring-purple-500`

Then rebuild: `npm run build`

## 🔍 Testing the CRUD

1. **Create**: Click "+ Add New Book" → Fill form → Save
2. **Read**: Books appear in the table automatically
3. **Update**: Click pencil icon → Edit → Save
4. **Delete**: Click trash icon → Confirm

Enjoy your CRUD system! 🎉
