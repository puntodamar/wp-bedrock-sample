# REST API Documentation

This directory contains the WordPress REST API controllers for the Books CRUD system.

## Overview

The Books CRUD system has been **migrated from the legacy `admin-ajax.php` approach to the modern WordPress REST API**. This provides better performance, cacheability, and follows WordPress best practices.

## API Endpoints

### Books Endpoints

Base URL: `/wp-json/sage/v1/books`

#### Get All Books
- **Method:** `GET`
- **URL:** `/wp-json/sage/v1/books`
- **Permission:** Public (no authentication required)
- **Response:**
```json
[
  {
    "id": 1,
    "title": "Book Title",
    "description": "Book description",
    "authors": [
      {
        "id": 1,
        "name": "Author Name"
      }
    ],
    "author_ids": [1],
    "isbn": "978-3-16-148410-0",
    "publication_year": "2024"
  }
]
```

#### Get Single Book
- **Method:** `GET`
- **URL:** `/wp-json/sage/v1/books/{id}`
- **Permission:** Public
- **Response:** Single book object (same structure as above)

#### Create Book
- **Method:** `POST`
- **URL:** `/wp-json/sage/v1/books`
- **Permission:** Requires `edit_posts` capability
- **Headers:**
  - `Content-Type: application/json`
  - `X-WP-Nonce: {nonce}`
- **Body:**
```json
{
  "title": "Book Title",
  "description": "Book description",
  "isbn": "978-3-16-148410-0",
  "publication_year": "2024",
  "author_ids": [1, 2]
}
```
- **Response:**
```json
{
  "message": "Book created successfully",
  "book": { /* book object */ }
}
```

#### Update Book
- **Method:** `PUT`
- **URL:** `/wp-json/sage/v1/books/{id}`
- **Permission:** Requires `edit_posts` capability
- **Headers:**
  - `Content-Type: application/json`
  - `X-WP-Nonce: {nonce}`
- **Body:** Same as Create Book
- **Response:**
```json
{
  "message": "Book updated successfully",
  "book": { /* book object */ }
}
```

#### Delete Book
- **Method:** `DELETE`
- **URL:** `/wp-json/sage/v1/books/{id}`
- **Permission:** Requires `delete_posts` capability
- **Headers:**
  - `X-WP-Nonce: {nonce}`
- **Response:**
```json
{
  "message": "Book deleted successfully",
  "id": 1
}
```

### Authors Endpoints

Base URL: `/wp-json/sage/v1/authors`

#### Get All Authors
- **Method:** `GET`
- **URL:** `/wp-json/sage/v1/authors`
- **Permission:** Public
- **Response:**
```json
[
  {
    "id": 1,
    "name": "Author Name"
  }
]
```

#### Get Single Author
- **Method:** `GET`
- **URL:** `/wp-json/sage/v1/authors/{id}`
- **Permission:** Public
- **Response:** Single author object

#### Create Author
- **Method:** `POST`
- **URL:** `/wp-json/sage/v1/authors`
- **Permission:** Requires `edit_posts` capability
- **Headers:**
  - `Content-Type: application/json`
  - `X-WP-Nonce: {nonce}`
- **Body:**
```json
{
  "name": "Author Name"
}
```
- **Response:**
```json
{
  "message": "Author created successfully",
  "author": {
    "id": 1,
    "name": "Author Name"
  }
}
```

## Authentication

The REST API uses WordPress nonces for authentication. The nonce is generated using:

```php
wp_create_nonce('wp_rest')
```

And sent in the `X-WP-Nonce` header:

```javascript
headers: {
  'X-WP-Nonce': window.restNonce
}
```

## File Structure

```
app/Api/
├── BooksController.php      # Handles all book-related endpoints
├── AuthorsController.php    # Handles all author-related endpoints
├── index.php                # Registers routes on rest_api_init
└── README.md                # This file
```

## Benefits Over admin-ajax.php

1. **RESTful Design:** Proper HTTP methods (GET, POST, PUT, DELETE)
2. **Better Performance:** GET requests can be cached
3. **Discoverable:** Endpoints visible at `/wp-json/`
4. **Standard Responses:** Consistent JSON responses
5. **Better Error Handling:** HTTP status codes (200, 201, 400, 404, 500)
6. **Modern:** Follows WordPress best practices since WP 4.7+

## Testing the API

You can test the API using:

### Browser (GET requests only)
```
https://yoursite.com/wp-json/sage/v1/books
https://yoursite.com/wp-json/sage/v1/authors
```

### cURL
```bash
# Get all books
curl https://yoursite.com/wp-json/sage/v1/books

# Create a book (requires authentication)
curl -X POST https://yoursite.com/wp-json/sage/v1/books \
  -H "Content-Type: application/json" \
  -H "X-WP-Nonce: YOUR_NONCE" \
  -d '{"title":"New Book","description":"Description","isbn":"123","publication_year":"2024","author_ids":[1]}'
```

### Postman or Insomnia
Import the endpoints and test with proper headers.

## Migration Notes

The old `admin-ajax.php` handlers in `app/Ajax/` have been **deprecated** and commented out in `crud-bootstrap.php`. They can be safely deleted once you've confirmed the REST API is working correctly.

### Old Approach (Deprecated)
```javascript
// OLD - admin-ajax.php
fetch(window.ajaxUrl, {
  method: 'POST',
  body: formData
})
```

### New Approach (Current)
```javascript
// NEW - REST API
fetch(`${window.restUrl}books`, {
  method: 'GET',
  headers: {
    'Content-Type': 'application/json',
    'X-WP-Nonce': window.restNonce
  }
})
```

## Error Handling

The API returns proper HTTP status codes:

- `200` - Success (GET, PUT, DELETE)
- `201` - Created (POST)
- `400` - Bad Request (validation errors)
- `403` - Forbidden (permission denied)
- `404` - Not Found
- `500` - Internal Server Error

Error responses include a message:
```json
{
  "code": "book_not_found",
  "message": "Book not found",
  "data": {
    "status": 404
  }
}
```
