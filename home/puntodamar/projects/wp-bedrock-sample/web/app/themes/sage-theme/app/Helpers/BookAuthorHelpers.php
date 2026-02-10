<?php

/**
 * Book-Author Helper Functions
 */

namespace App\Helpers;

function get_book_authors($book_id)
{
    $author_ids = get_post_meta($book_id, 'author_ids', true);
    $authors = [];

    if (!empty($author_ids) && is_array($author_ids)) {
        foreach ($author_ids as $author_id) {
            $author_post = get_post($author_id);
            if ($author_post && $author_post->post_type === 'author') {
                $authors[] = [
                    'id'   => $author_id,
                    'name' => $author_post->post_title,
                ];
            }
        }
    }

    return $authors;
}
