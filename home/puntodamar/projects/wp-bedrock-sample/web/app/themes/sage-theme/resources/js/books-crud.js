/**
 * Books CRUD JavaScript
 *
 * This file handles all client-side functionality for the Books CRUD interface.
 * It uses vanilla JavaScript (no jQuery) and the Fetch API for AJAX requests.
 *
 * Main functions:
 * - loadBooks(): Fetches and displays all books
 * - openModal(): Opens the create/edit modal
 * - closeModal(): Closes the modal
 * - saveBook(): Saves a new or updated book
 * - editBook(): Populates the modal with book data for editing
 * - deleteBook(): Deletes a book after confirmation
 * - showMessage(): Displays success/error messages
 */

/**
 * Load all books from the server and display them in the table
 *
 * This function is called when the page loads and after any CRUD operation
 * to refresh the book list.
 */
function loadBooks() {
  /**
   * Create FormData object to send with the AJAX request
   * FormData is used to easily construct key/value pairs for POST requests
   */
  const formData = new FormData();
  formData.append('action', 'get_books'); // WordPress AJAX action name
  formData.append('nonce', window.bookNonce); // Security nonce

  /**
   * Make AJAX request using Fetch API
   * Fetch is a modern replacement for XMLHttpRequest
   *
   * fetch() returns a Promise, so we use .then() to handle the response
   */
  fetch(window.ajaxUrl, {
    method: 'POST', // HTTP method
    body: formData, // Data to send
  })
    .then((response) => response.json()) // Parse JSON response
    .then((data) => {
      /**
       * Check if the request was successful
       * WordPress AJAX returns data.success = true/false
       */
      if (data.success) {
        displayBooks(data.data); // Display the books
      } else {
        showMessage('Failed to load books', 'error');
      }
    })
    .catch((error) => {
      /**
       * Handle any errors that occurred during the request
       * This catches network errors, JSON parsing errors, etc.
       */
      console.error('Error loading books:', error);
      showMessage('Error loading books', 'error');
    });
}

/**
 * Display books in the table
 *
 * @param {Array} books - Array of book objects from the server
 */
function displayBooks(books) {
  // Get the table body element where we'll insert rows
  const tbody = document.getElementById('books-table-body');

  /**
   * Clear existing content
   * innerHTML = '' removes all child elements
   */
  tbody.innerHTML = '';

  /**
   * Check if there are any books to display
   */
  if (books.length === 0) {
    // Display "no books" message
    tbody.innerHTML = `
      <tr>
        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
          <div class="flex flex-col items-center">
            <svg class="h-12 w-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
            <p class="text-lg font-medium">No books found</p>
            <p class="text-sm">Click "Add New Book" to create your first book</p>
          </div>
        </td>
      </tr>
    `;
    return;
  }

  /**
   * Loop through each book and create a table row
   * forEach() executes a function for each element in the array
   */
  books.forEach((book) => {
    /**
     * Create a new table row element
     * We use template literals (backticks) to create HTML with embedded variables
     */
    const row = document.createElement('tr');
    row.className = 'hover:bg-gray-50 transition duration-150';

    /**
     * Truncate description if it's too long
     * This prevents the table from becoming too wide
     */
    const shortDescription = book.description.length > 100
      ? book.description.substring(0, 100) + '...'
      : book.description;

    /**
     * Set the row's HTML content
     * ${variable} syntax inserts JavaScript variables into the string
     */
    row.innerHTML = `
      <td class="px-6 py-4 whitespace-nowrap">
        <div class="text-sm font-medium text-gray-900">${escapeHtml(book.title)}</div>
      </td>
      <td class="px-6 py-4 whitespace-nowrap">
        <div class="text-sm text-gray-600">${escapeHtml(book.author)}</div>
      </td>
      <td class="px-6 py-4 whitespace-nowrap">
        <div class="text-sm text-gray-600">${escapeHtml(book.isbn)}</div>
      </td>
      <td class="px-6 py-4 whitespace-nowrap">
        <div class="text-sm text-gray-600">${escapeHtml(book.publication_year)}</div>
      </td>
      <td class="px-6 py-4">
        <div class="text-sm text-gray-600 max-w-xs">${escapeHtml(shortDescription)}</div>
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
        <div class="flex space-x-2">
          <button
            onclick="editBook(${book.id})"
            class="text-blue-600 hover:text-blue-900 transition duration-150"
            title="Edit book"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
          </button>
          <button
            onclick="deleteBook(${book.id})"
            class="text-red-600 hover:text-red-900 transition duration-150"
            title="Delete book"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
          </button>
        </div>
      </td>
    `;

    // Add the row to the table body
    tbody.appendChild(row);
  });
}

/**
 * Escape HTML to prevent XSS attacks
 *
 * This function converts special characters to HTML entities
 * to prevent malicious scripts from being executed.
 *
 * @param {string} text - Text to escape
 * @returns {string} - Escaped text
 */
function escapeHtml(text) {
  if (!text) return '';

  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

/**
 * Open the modal for creating or editing a book
 *
 * @param {number|null} bookId - Book ID if editing, null if creating new
 */
function openModal(bookId = null) {
  const modal = document.getElementById('book-modal');
  const modalTitle = document.getElementById('modal-title');
  const form = document.getElementById('book-form');

  /**
   * Reset the form
   * This clears all input fields
   */
  form.reset();
  document.getElementById('book-id').value = '';

  /**
   * Set modal title based on whether we're creating or editing
   */
  if (bookId) {
    modalTitle.textContent = 'Edit Book';
  } else {
    modalTitle.textContent = 'Add New Book';
  }

  /**
   * Show the modal
   * Remove 'hidden' class to make it visible
   */
  modal.classList.remove('hidden');
}

/**
 * Close the modal
 */
function closeModal() {
  const modal = document.getElementById('book-modal');
  modal.classList.add('hidden');
}

/**
 * Edit a book
 *
 * This function fetches the book data and populates the modal form
 *
 * @param {number} bookId - ID of the book to edit
 */
function editBook(bookId) {
  /**
   * Create FormData for the AJAX request
   */
  const formData = new FormData();
  formData.append('action', 'get_books');
  formData.append('nonce', window.bookNonce);

  /**
   * Fetch all books and find the one we want to edit
   * In a production app, you might want a separate endpoint to get a single book
   */
  fetch(window.ajaxUrl, {
    method: 'POST',
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        /**
         * Find the book with the matching ID
         * find() returns the first element that matches the condition
         */
        const book = data.data.find((b) => b.id == bookId);

        if (book) {
          /**
           * Populate the form fields with book data
           */
          document.getElementById('book-id').value = book.id;
          document.getElementById('book-title').value = book.title;
          document.getElementById('book-author').value = book.author;
          document.getElementById('book-isbn').value = book.isbn;
          document.getElementById('book-year').value = book.publication_year;
          document.getElementById('book-description').value = book.description;

          /**
           * Open the modal
           */
          openModal(bookId);
        }
      }
    })
    .catch((error) => {
      console.error('Error loading book:', error);
      showMessage('Error loading book data', 'error');
    });
}

/**
 * Delete a book
 *
 * @param {number} bookId - ID of the book to delete
 */
function deleteBook(bookId) {
  /**
   * Ask for confirmation before deleting
   * confirm() shows a browser dialog with OK/Cancel buttons
   */
  if (!confirm('Are you sure you want to delete this book? This action cannot be undone.')) {
    return; // User clicked Cancel
  }

  /**
   * Create FormData for the delete request
   */
  const formData = new FormData();
  formData.append('action', 'delete_book');
  formData.append('nonce', window.bookNonce);
  formData.append('id', bookId);

  /**
   * Send delete request to server
   */
  fetch(window.ajaxUrl, {
    method: 'POST',
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showMessage(data.data.message, 'success');
        loadBooks(); // Reload the book list
      } else {
        showMessage(data.data.message || 'Failed to delete book', 'error');
      }
    })
    .catch((error) => {
      console.error('Error deleting book:', error);
      showMessage('Error deleting book', 'error');
    });
}

/**
 * Show a success or error message
 *
 * @param {string} message - Message to display
 * @param {string} type - 'success' or 'error'
 */
function showMessage(message, type) {
  const container = document.getElementById('message-container');
  const messageDiv = document.getElementById('message');

  /**
   * Set message content and styling based on type
   */
  messageDiv.textContent = message;

  if (type === 'success') {
    messageDiv.className = 'p-4 rounded-lg bg-green-100 text-green-800 border border-green-200';
  } else {
    messageDiv.className = 'p-4 rounded-lg bg-red-100 text-red-800 border border-red-200';
  }

  /**
   * Show the message
   */
  container.classList.remove('hidden');

  /**
   * Hide the message after 5 seconds
   * setTimeout() executes a function after a delay (in milliseconds)
   */
  setTimeout(() => {
    container.classList.add('hidden');
  }, 5000);
}

/**
 * Handle form submission
 *
 * This event listener is attached to the form and prevents the default
 * form submission behavior, instead using AJAX to submit the data.
 */
document.getElementById('book-form').addEventListener('submit', function (e) {
  /**
   * Prevent default form submission
   * By default, forms reload the page when submitted
   */
  e.preventDefault();

  /**
   * Get form data
   * FormData automatically collects all form field values
   */
  const formData = new FormData(this);

  /**
   * Get the book ID to determine if we're creating or updating
   */
  const bookId = document.getElementById('book-id').value;

  /**
   * Set the appropriate AJAX action
   */
  if (bookId) {
    formData.append('action', 'update_book');
    formData.append('id', bookId);
  } else {
    formData.append('action', 'create_book');
  }

  /**
   * Add security nonce
   */
  formData.append('nonce', window.bookNonce);

  /**
   * Disable submit button to prevent double-submission
   */
  const submitButton = this.querySelector('button[type="submit"]');
  const submitText = document.getElementById('submit-text');
  const originalText = submitText.textContent;
  submitText.textContent = 'Saving...';
  submitButton.disabled = true;

  /**
   * Send the request to the server
   */
  fetch(window.ajaxUrl, {
    method: 'POST',
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showMessage(data.data.message, 'success');
        closeModal();
        loadBooks(); // Reload the book list
      } else {
        showMessage(data.data.message || 'Failed to save book', 'error');
      }
    })
    .catch((error) => {
      console.error('Error saving book:', error);
      showMessage('Error saving book', 'error');
    })
    .finally(() => {
      /**
       * Re-enable submit button
       * finally() runs regardless of success or failure
       */
      submitText.textContent = originalText;
      submitButton.disabled = false;
    });
});

/**
 * Load books when the page loads
 *
 * DOMContentLoaded event fires when the HTML document has been completely parsed
 * This ensures all elements exist before we try to manipulate them
 */
document.addEventListener('DOMContentLoaded', function () {
  loadBooks();
});

/**
 * Close modal when clicking outside of it
 *
 * This provides a better user experience by allowing users to close
 * the modal by clicking the dark overlay
 */
document.getElementById('book-modal').addEventListener('click', function (e) {
  /**
   * Check if the click was on the overlay (not the modal content)
   * e.target is the element that was clicked
   * this is the modal overlay element
   */
  if (e.target === this) {
    closeModal();
  }
});

/**
 * Close modal with Escape key
 *
 * This is a common UX pattern for modals
 */
document.addEventListener('keydown', function (e) {
  /**
   * Check if Escape key was pressed and modal is open
   */
  if (e.key === 'Escape') {
    const modal = document.getElementById('book-modal');
    if (!modal.classList.contains('hidden')) {
      closeModal();
    }
  }
});

/**
 * Expose functions to global scope
 *
 * When using Vite, JavaScript modules are scoped by default.
 * To allow inline onclick handlers to access these functions,
 * we need to attach them to the window object.
 */
window.openModal = openModal;
window.closeModal = closeModal;
window.editBook = editBook;
window.deleteBook = deleteBook;
