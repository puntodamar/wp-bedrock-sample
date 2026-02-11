@php
  // Get book meta data
  $book_description = get_post_meta(get_the_ID(), 'book_description', true);
  $isbn = get_post_meta(get_the_ID(), 'isbn', true);
  $publication_year = get_post_meta(get_the_ID(), 'publication_year', true);
  $author_ids = get_post_meta(get_the_ID(), 'book_authors', true);

  // Get author names
  $authors = [];
  if (!empty($author_ids) && is_array($author_ids)) {
      foreach ($author_ids as $author_id) {
          $author = get_post($author_id);
          if ($author) {
              $authors[] = [
                  'id' => $author_id,
                  'name' => $author->post_title,
                  'link' => get_permalink($author_id),
              ];
          }
      }
  }
@endphp

<article @php(post_class('book-single'))>
  <header class="book-header">
    <h1 class="book-title">
      {!! $title !!}
    </h1>

    @if (!empty($authors))
      <div class="book-authors">
        <strong>{{ __('Author(s):', 'sage') }}</strong>
        @foreach ($authors as $index => $author)
          <a href="{{ $author['link'] }}">{{ $author['name'] }}</a>@if ($index < count($authors) - 1), @endif
        @endforeach
      </div>
    @endif

    <div class="book-meta">
      @if ($isbn)
        <div class="book-isbn">
          <strong>{{ __('ISBN:', 'sage') }}</strong> {{ $isbn }}
        </div>
      @endif

      @if ($publication_year)
        <div class="book-publication-year">
          <strong>{{ __('Publication Year:', 'sage') }}</strong> {{ $publication_year }}
        </div>
      @endif
    </div>
  </header>

  @if (has_post_thumbnail())
    <div class="book-thumbnail">
      {!! get_the_post_thumbnail(null, 'large', ['class' => 'book-cover']) !!}
    </div>
  @endif

  @if ($book_description)
    <div class="book-description">
      <h2>{{ __('Description', 'sage') }}</h2>
      <div class="book-description-content">
        {!! wpautop($book_description) !!}
      </div>
    </div>
  @elseif (get_the_content())
    <div class="book-description">
      <h2>{{ __('Description', 'sage') }}</h2>
      <div class="book-description-content">
        @php(the_content())
      </div>
    </div>
  @endif

  @if ($pagination())
    <footer>
      <nav class="page-nav" aria-label="Page">
        {!! $pagination !!}
      </nav>
    </footer>
  @endif
</article>

<style>
  .book-single {
    max-width: 800px;
    margin: 0 auto;
    padding: 2rem;
  }

  .book-header {
    margin-bottom: 2rem;
  }

  .book-title {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    line-height: 1.2;
  }

  .book-authors {
    font-size: 1.2rem;
    margin-bottom: 1rem;
    color: #666;
  }

  .book-authors a {
    color: #0073aa;
    text-decoration: none;
  }

  .book-authors a:hover {
    text-decoration: underline;
  }

  .book-meta {
    display: flex;
    gap: 2rem;
    margin-top: 1rem;
    padding: 1rem;
    background: #f5f5f5;
    border-radius: 4px;
  }

  .book-meta strong {
    color: #333;
  }

  .book-thumbnail {
    margin: 2rem 0;
    text-align: center;
  }

  .book-cover {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  }

  .book-description {
    margin-top: 2rem;
  }

  .book-description h2 {
    font-size: 1.8rem;
    margin-bottom: 1rem;
    border-bottom: 2px solid #0073aa;
    padding-bottom: 0.5rem;
  }

  .book-description-content {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #333;
  }

  .book-description-content p {
    margin-bottom: 1rem;
  }

  .book-description-content ul,
  .book-description-content ol {
    margin-left: 2rem;
    margin-bottom: 1rem;
  }

  .book-description-content blockquote {
    border-left: 4px solid #0073aa;
    padding-left: 1rem;
    margin: 1.5rem 0;
    font-style: italic;
    color: #666;
  }
</style>
