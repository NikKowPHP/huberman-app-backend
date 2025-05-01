<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;

class PostService
{
    public function createPost(array $data): Post
    {
        return Post::create([
            'user_id' => Auth::id(),
            'title' => $data['title'],
            'content' => $data['content'],
            'status' => 'published'
        ]);
    }

    public function createComment(Post $post, array $data): Comment
    {
        return $post->comments()->create([
            'user_id' => Auth::id(),
            'content' => $data['content']
        ]);
    }

    public function getPostsWithComments()
    {
        return Post::with(['user', 'comments.user'])
            ->latest()
            ->paginate(10);
    }
}
