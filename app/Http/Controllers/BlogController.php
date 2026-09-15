<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::query()
            ->where('status', 'published')
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->category))
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        return view('blog.index', compact('posts'));
    }

    public function show(string $slug)
    {
        $post = Post::query()->where('slug', $slug)->where('status', 'published')->firstOrFail();

        return view('blog.show', compact('post'));
    }
}
