/**
 * Books CRUD JavaScript with Author Management
 * Using WordPress REST API
 */

let allAuthors = []; // Store all authors globally

/**
 * Load all authors from the server
 */
function loadAuthors() {
  console.log('Loading authors from:', `${window.restUrl}authors`);
  console.log('Using nonce:', window.restNonce);

  return fetch(`${window.restUrl}authors`, {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
      'X-WP-Nonce': window.restNonce,
    },
  })
    .then((response) => {
      console.log('Authors response status:', response.status);
      console.log('Authors response headers:', response.headers.get('content-type'));

      if (!response.ok) {
        // Log the response for debugging
        return response.text().then(text => {
          console.error('Authors response status:', response.status);
          console.error('Authors response text:', text);
          throw new Error(`HTTP error! status: ${response.status}`);
        });
      }

      // Check if response is actually JSON
      const contentType = response.headers.get('content-type');
      if (!contentType || !contentType.includes('application/json')) {
        return response.text().then(text => {
          console.error('Expected JSON but got:', contentType);
          console.error('Authors response text:', text);
          throw new Error('Response is not JSON');
        });
      }

      return response.json();
    })
    .then((data) => {
      console.log('Authors data received:', data);
      allAuthors = data;
      populateAuthorDropdown();
      return allAuthors;
    })
    .catch((error) => {
      console.error('Error loading authors:', error);
      return [];
    });
}

/**
 * Populate the author dropdown with options
 */
function populateAuthorDropdown(selectedIds = []) {
  const select = document.getElementById('book-authors');
  if (!select) return;

  select.innerHTML = '';

  allAuthors.forEach((author) => {
    const option = document.createElement('option');
    option.value = author.id;
    option.textContent = author.name;
    if (selectedIds.includes(author.id)) {
      option.selected = true;
    }
    select.appendChild(option);
  });
}

/**
 * Load all books from the server and display them in the table
 */
function loadBooks() {
  console.log('Loading books from:', `${window.restUrl}books`);

  fetch(`${window.restUrl}books`, {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
      'X-WP-Nonce': window.restNonce,
    },
  })
    .then((response) => {
      console.log('Books response status:', response.status);
      console.log('Books response headers:', response.headers.get('content-type'));

      if (!response.ok) {
        // Log the response for debugging
        return response.text().then(text => {
          console.error('Books response status:', response.status);
          console.error('Books response text:', text);
          throw new Error(`HTTP error! status: ${response.status}`);
        });
      }

      // Check if response is actually JSON
      const contentType = response.headers.get('content-type');
      if (!contentType || !contentType.includes('application/json')) {
        return response.text().then(text => {
          console.error('Expected JSON but got:', contentType);
          console.error('Response text:', text);
          throw new Error('Response is not JSON');
        });
      }

      return response.json();
    })
    .then((data) => {
      console.log('Books data received:', data);
      displayBooks(data);
    })
    .catch((error) => {
      console.error('Error loading books:', error);
      showMessage('aaaError loading books', 'error');
    });
}

/**
 * Display books in the table
 */
function displayBooks(books) {
  const tbody = document.getElementById('books-table-body');
  tbody.innerHTML = '';

  if (books.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
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

  books.forEach((book) => {
    const row = document.createElement('tr');
    row.className = 'hover:bg-gray-50 transition duration-150';

    // Format authors as comma-separated list
    const authorNames = book.authors && book.authors.length > 0
      ? book.authors.map(a => a.name).join(', ')
      : 'No authors';

    row.innerHTML = `
      <td class="px-6 py-4 whitespace-nowrap">
        <div class="text-sm font-medium text-gray-900">${escapeHtml(book.title)}</div>
      </td>
      <td class="px-6 py-4 whitespace-nowrap">
        <div class="text-sm text-gray-600">${escapeHtml(authorNames)}</div>
      </td>
      <td class="px-6 py-4 whitespace-nowrap">
        <div class="text-sm text-gray-600">${escapeHtml(book.isbn)}</div>
      </td>
      <td class="px-6 py-4 whitespace-nowrap">
        <div class="text-sm text-gray-600">${escapeHtml(book.publication_year)}</div>
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
        <div class="flex space-x-2">
          <button
            onclick="viewBook(${book.id})"
            class="text-green-600 hover:text-green-900 transition duration-150"
            title="View book details"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
          </button>
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

    tbody.appendChild(row);
  });
}

/**
 * Escape HTML to prevent XSS attacks
 */
function escapeHtml(text) {
  if (!text) return '';
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

/**
 * Open the modal for creating or editing a book
 */
function openModal(bookId = null) {
  const modal = document.getElementById('book-modal');
  const modalTitle = document.getElementById('modal-title');
  const form = document.getElementById('book-form');

  form.reset();
  document.getElementById('book-id').value = '';
  populateAuthorDropdown();

  if (bookId) {
    modalTitle.textContent = 'Edit Book';
  } else {
    modalTitle.textContent = 'Add New Book';
  }

  modal.classList.remove('hidden');
}

/**
 * Close the book modal
 */
function closeModal() {
  const modal = document.getElementById('book-modal');
  modal.classList.add('hidden');
}

/**
 * Open the view modal to display book details
 */
function openViewModal(book) {
  const modal = document.getElementById('view-modal');

  // Populate the modal with book data
  document.getElementById('view-title').textContent = book.title;
  document.getElementById('view-authors').textContent = book.authors && book.authors.length > 0
    ? book.authors.map(a => a.name).join(', ')
    : 'No authors';
  document.getElementById('view-isbn').textContent = book.isbn || 'N/A';
  document.getElementById('view-year').textContent = book.publication_year || 'N/A';
  document.getElementById('view-description').textContent = book.description || 'No description available';

  modal.classList.remove('hidden');
}

/**
 * Close the view modal
 */
function closeViewModal() {
  const modal = document.getElementById('view-modal');
  modal.classList.add('hidden');
}

/**
 * Open the author modal
 */
function openAuthorModal() {
  const modal = document.getElementById('author-modal');
  const form = document.getElementById('author-form');
  form.reset();
  modal.classList.remove('hidden');
}

/**
 * Close the author modal
 */
function closeAuthorModal() {
  const modal = document.getElementById('author-modal');
  modal.classList.add('hidden');
}

/**
 * View a book's full details
 */
function viewBook(bookId) {
  bookId = parseInt(bookId);

  fetch(`${window.restUrl}books/${bookId}`, {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
      'X-WP-Nonce': window.restNonce,
    },
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then((book) => {
      openViewModal(book);
    })
    .catch((error) => {
      console.error('Error loading book:', error);
      showMessage('Error loading book details', 'error');
    });
}

/**
 * Edit a book
 */
function editBook(bookId) {
  bookId = parseInt(bookId);

  fetch(`${window.restUrl}books/${bookId}`, {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
      'X-WP-Nonce': window.restNonce,
    },
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then((book) => {
      requestAnimationFrame(() => {
        document.getElementById('book-id').value = book.id;
        document.getElementById('book-title').value = book.title;
        document.getElementById('book-isbn').value = book.isbn;
        document.getElementById('book-year').value = book.publication_year;
        document.getElementById('book-description').value = book.description;

        // Select the authors
        const authorIds = book.author_ids || [];
        populateAuthorDropdown(authorIds);
      });

      openModal(bookId);
    })
    .catch((error) => {
      console.error('Error loading book:', error);
      showMessage('Error loading book data', 'error');
    });
}

/**
 * Delete a book
 */
function deleteBook(bookId) {
  if (!confirm('Are you sure you want to delete this book? This action cannot be undone.')) {
    return;
  }

  fetch(`${window.restUrl}books/${bookId}`, {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/json',
      'X-WP-Nonce': window.restNonce,
    },
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then((data) => {
      showMessage(data.message, 'success');
      loadBooks();
    })
    .catch((error) => {
      console.error('Error deleting book:', error);
      showMessage('Error deleting book', 'error');
    });
}

/**
 * Show a success or error message
 */
function showMessage(message, type) {
  const container = document.getElementById('message-container');
  const messageDiv = document.getElementById('message');

  messageDiv.textContent = message;

  if (type === 'success') {
    messageDiv.className = 'p-4 rounded-lg bg-green-100 text-green-800 border border-green-200';
  } else {
    messageDiv.className = 'p-4 rounded-lg bg-red-100 text-red-800 border border-red-200';
  }

  container.classList.remove('hidden');

  setTimeout(() => {
    container.classList.add('hidden');
  }, 5000);
}

/**
 * Handle book form submission
 */
document.getElementById('book-form').addEventListener('submit', function (e) {
  e.preventDefault();

  const bookId = document.getElementById('book-id').value;

  // Get selected author IDs
  const select = document.getElementById('book-authors');
  const selectedOptions = Array.from(select.selectedOptions);
  const authorIds = selectedOptions.map(option => parseInt(option.value));

  // Prepare data as JSON
  const bookData = {
    title: document.getElementById('book-title').value,
    description: document.getElementById('book-description').value,
    isbn: document.getElementById('book-isbn').value,
    publication_year: document.getElementById('book-year').value,
    author_ids: authorIds,
  };

  const submitButton = this.querySelector('button[type="submit"]');
  const submitText = document.getElementById('submit-text');
  const originalText = submitText.textContent;
  submitText.textContent = 'Saving...';
  submitButton.disabled = true;

  const url = bookId ? `${window.restUrl}books/${bookId}` : `${window.restUrl}books`;
  const method = bookId ? 'PUT' : 'POST';

  fetch(url, {
    method: method,
    headers: {
      'Content-Type': 'application/json',
      'X-WP-Nonce': window.restNonce,
    },
    body: JSON.stringify(bookData),
  })
    .then((response) => {
      if (!response.ok) {
        return response.json().then(err => {
          throw new Error(err.message || 'Failed to save book');
        });
      }
      return response.json();
    })
    .then((data) => {
      showMessage(data.message, 'success');
      closeModal();
      loadBooks();
    })
    .catch((error) => {
      console.error('Error saving book:', error);
      showMessage(error.message || 'Error saving book', 'error');
    })
    .finally(() => {
      submitText.textContent = originalText;
      submitButton.disabled = false;
    });
});

/**
 * Handle author form submission
 */
document.getElementById('author-form').addEventListener('submit', function (e) {
  e.preventDefault();

  const authorData = {
    name: document.getElementById('author-name').value,
  };

  const submitButton = this.querySelector('button[type="submit"]');
  const submitText = document.getElementById('author-submit-text');
  const originalText = submitText.textContent;
  submitText.textContent = 'Creating...';
  submitButton.disabled = true;

  fetch(`${window.restUrl}authors`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-WP-Nonce': window.restNonce,
    },
    body: JSON.stringify(authorData),
  })
    .then((response) => {
      if (!response.ok) {
        return response.json().then(err => {
          throw new Error(err.message || 'Failed to create author');
        });
      }
      return response.json();
    })
    .then((data) => {
      showMessage(data.message, 'success');
      closeAuthorModal();
      // Reload authors and update dropdown
      loadAuthors().then(() => {
        // Select the newly created author
        const newAuthorId = data.author.id;
        const select = document.getElementById('book-authors');
        const option = Array.from(select.options).find(opt => opt.value == newAuthorId);
        if (option) {
          option.selected = true;
        }
      });
    })
    .catch((error) => {
      console.error('Error creating author:', error);
      showMessage(error.message || 'Error creating author', 'error');
    })
    .finally(() => {
      submitText.textContent = originalText;
      submitButton.disabled = false;
    });
});

/**
 * Load books and authors when the page loads
 */
document.addEventListener('DOMContentLoaded', function () {
  loadAuthors().then(() => {
    loadBooks();
  });
});

/**
 * Close modals when clicking outside
 */
document.getElementById('book-modal').addEventListener('click', function (e) {
  if (e.target === this) {
    closeModal();
  }
});

document.getElementById('view-modal').addEventListener('click', function (e) {
  if (e.target === this) {
    closeViewModal();
  }
});

document.getElementById('author-modal').addEventListener('click', function (e) {
  if (e.target === this) {
    closeAuthorModal();
  }
});

/**
 * Close modals with Escape key
 */
document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') {
    const bookModal = document.getElementById('book-modal');
    const viewModal = document.getElementById('view-modal');
    const authorModal = document.getElementById('author-modal');

    if (!bookModal.classList.contains('hidden')) {
      closeModal();
    }
    if (!viewModal.classList.contains('hidden')) {
      closeViewModal();
    }
    if (!authorModal.classList.contains('hidden')) {
      closeAuthorModal();
    }
  }
});

// Export functions to window for onclick handlers
window.openModal = openModal;
window.viewBook = viewBook;
window.editBook = editBook;
window.closeModal = closeModal;
window.closeViewModal = closeViewModal;
window.deleteBook = deleteBook;
window.openAuthorModal = openAuthorModal;
window.closeAuthorModal = closeAuthorModal;
