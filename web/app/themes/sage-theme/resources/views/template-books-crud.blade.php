{{--
  Template Name: Books CRUD

  This is a custom page template for managing books.
  To use this template:
  1. Create a new page in WordPress admin
  2. Select "Books CRUD" from the Template dropdown
  3. Publish the page

  The template uses Tailwind CSS for styling and vanilla JavaScript for AJAX operations.
--}}

@extends('layouts.app')

@section('content')
  {{--
    Main container with padding and max width
    - container: Centers content and adds responsive padding
    - mx-auto: Centers the container horizontally
    - px-4: Adds horizontal padding
    - py-8: Adds vertical padding
  --}}
  <div class="container mx-auto px-4 py-8">

    {{-- Page header --}}
    <div class="mb-8">
      <h1 class="text-4xl font-bold text-gray-900 mb-2">Book Management</h1>
      <p class="text-gray-600">Create, read, update, and delete books in your library</p>
    </div>

    {{--
      Success/Error message container
      This div is hidden by default and shown via JavaScript when needed
    --}}
    <div id="message-container" class="hidden mb-6">
      <div id="message" class="p-4 rounded-lg"></div>
    </div>

    {{--
      Add New Book Button
      Opens the modal form for creating a new book
    --}}
    <div class="mb-6">
      <button
        onclick="openModal()"
        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg shadow-md transition duration-200 ease-in-out transform hover:scale-105"
      >
        + Add New Book
      </button>
    </div>

    {{--
      Books Table
      Displays all books in a responsive table format
      - overflow-x-auto: Allows horizontal scrolling on small screens
      - shadow-lg: Adds a large shadow for depth
      - rounded-lg: Rounds the corners
    --}}
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          {{-- Table header --}}
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ISBN</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Year</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          {{--
            Table body
            This will be populated dynamically via JavaScript
          --}}
          <tbody id="books-table-body" class="bg-white divide-y divide-gray-200">
            {{-- Loading state --}}
            <tr id="loading-row">
              <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                <div class="flex items-center justify-center">
                  <svg class="animate-spin h-5 w-5 mr-3 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Loading books...
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    {{--
      Modal for Create/Edit Book
      This modal is hidden by default and shown when user clicks "Add New Book" or "Edit"

      Modal structure:
      - Overlay: Dark background that covers the page
      - Modal container: The actual modal box
      - Form: Contains all input fields
    --}}
    <div id="book-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      {{-- Modal container --}}
      <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white">

        {{-- Modal header --}}
        <div class="flex items-center justify-between mb-4">
          <h3 id="modal-title" class="text-2xl font-bold text-gray-900">Add New Book</h3>
          {{-- Close button --}}
          <button
            onclick="closeModal()"
            class="text-gray-400 hover:text-gray-600 transition duration-150"
          >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        {{-- Modal form --}}
        <form id="book-form" class="space-y-4">
          {{--
            Hidden field to store book ID when editing
            This field is empty when creating a new book
          --}}
          <input type="hidden" id="book-id" name="book_id">

          {{-- Title field --}}
          <div>
            <label for="book-title" class="block text-sm font-medium text-gray-700 mb-1">
              Title <span class="text-red-500">*</span>
            </label>
            <input
              type="text"
              id="book-title"
              name="title"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150"
              placeholder="Enter book title"
            >
          </div>

          {{-- Authors field --}}
          <div>
            <label for="book-authors" class="block text-sm font-medium text-gray-700 mb-1">
              Authors
            </label>
            <select
              id="book-authors"
              name="author_ids[]"
              multiple
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150"
              style="min-height: 120px;"
            >
              {{-- Options will be populated via JavaScript --}}
            </select>
            <p class="mt-1 text-sm text-gray-500">Hold Ctrl (Cmd on Mac) to select multiple authors</p>
            <button
              type="button"
              onclick="openAuthorModal()"
              class="mt-2 text-sm text-blue-600 hover:text-blue-800 font-medium"
            >
              + Add New Author
            </button>
          </div>

          {{-- ISBN field --}}
          <div>
            <label for="book-isbn" class="block text-sm font-medium text-gray-700 mb-1">
              ISBN
            </label>
            <input
              type="text"
              id="book-isbn"
              name="isbn"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150"
              placeholder="Enter ISBN"
            >
          </div>

          {{-- Publication Year field --}}
          <div>
            <label for="book-year" class="block text-sm font-medium text-gray-700 mb-1">
              Publication Year
            </label>
            <input
              type="text"
              id="book-year"
              name="publication_year"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150"
              placeholder="Enter publication year"
            >
          </div>

          {{-- Description field --}}
          <div>
            <label for="book-description" class="block text-sm font-medium text-gray-700 mb-1">
              Description
            </label>
            <textarea
              id="book-description"
              name="description"
              rows="4"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150"
              placeholder="Enter book description"
            ></textarea>
          </div>

          {{-- Form buttons --}}
          <div class="flex justify-end space-x-3 pt-4">
            {{-- Cancel button --}}
            <button
              type="button"
              onclick="closeModal()"
              class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition duration-150"
            >
              Cancel
            </button>
            {{-- Submit button --}}
            <button
              type="submit"
              class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-150"
            >
              <span id="submit-text">Save Book</span>
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>

  {{--
    Pass WordPress REST API URL and nonce to JavaScript
    These are needed for making REST API requests to WordPress
    IMPORTANT: This must come BEFORE @vite to ensure variables are defined
  --}}
  <script>
    // WordPress REST API URL - all API requests go to this endpoint
    // Example: /wp-json/sage/v1/
    window.restUrl = <?php echo json_encode(rest_url('sage/v1/')); ?>;

    // REST API nonce - WordPress uses this to verify requests are legitimate
    // This nonce is sent in the X-WP-Nonce header for authentication
    window.restNonce = <?php echo json_encode(wp_create_nonce('wp_rest')); ?>;

    console.log('REST URL set to:', window.restUrl);
    console.log('REST Nonce set to:', window.restNonce);
  </script>

  {{--
    Include the JavaScript file for AJAX operations
    @vite directive compiles and includes the JavaScript file
  --}}
  @vite('resources/js/books-crud.js')

    {{-- Modal for Add Author --}}
    <div id="author-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-2xl font-bold text-gray-900">Add New Author</h3>
          <button onclick="closeAuthorModal()" class="text-gray-400 hover:text-gray-600 transition duration-150">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <form id="author-form" class="space-y-4">
          <div>
            <label for="author-name" class="block text-sm font-medium text-gray-700 mb-1">
              Author Name <span class="text-red-500">*</span>
            </label>
            <input
              type="text"
              id="author-name"
              name="author_name"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150"
              placeholder="Enter author name"
            >
          </div>

          <div class="flex justify-end space-x-3 pt-4">
            <button type="button" onclick="closeAuthorModal()" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition duration-150">
              Cancel
            </button>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-150">
              <span id="author-submit-text">Add Author</span>
            </button>
          </div>
        </form>
      </div>
    </div>

@endsection
