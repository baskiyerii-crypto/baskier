<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\AiContentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminBlogController extends Controller
{
    public function index(Request $request)
    {
        if (! Schema::hasTable('posts')) {
            $posts = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);

            return view('admin.blog.index', compact('posts'));
        }

        $posts = Post::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->category))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.blog.index', compact('posts'));
    }

    public function create()
    {
        return view('admin.blog.create', ['post' => new Post(['status' => 'draft'])]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $validated['slug'] = $this->uniqueSlug($validated['slug'] ?? $validated['title']);
        $validated['ai_humanized'] = false;
        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }
        Post::create($validated);

        return redirect()->route('admin.blog.index')->with('success', __('panel.blog_created'));
    }

    public function edit(Post $post)
    {
        return view('admin.blog.edit', compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        $validated = $this->validated($request, $post);
        $validated['slug'] = $this->uniqueSlug($validated['slug'] ?? $validated['title'], $post->id);
        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = $post->published_at ?? now();
        }
        if ($validated['status'] === 'draft') {
            $validated['published_at'] = null;
        }
        $post->update($validated);

        return redirect()->route('admin.blog.index')->with('success', __('panel.blog_updated'));
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('admin.blog.index')->with('success', __('panel.blog_deleted'));
    }

    public function importForm()
    {
        return view('admin.blog.import');
    }

    public function import(Request $request, AiContentService $ai)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx'],
            'humanize' => ['boolean'],
            'status' => ['required', 'in:draft,published'],
            'category_filter' => ['nullable', 'string', 'max:120'],
        ]);

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle) ?: [];
        $header = array_map(fn ($h) => Str::lower(trim((string) $h)), $header);
        $count = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $data = [];
            foreach ($header as $i => $key) {
                $data[$key] = $row[$i] ?? '';
            }
            $title = $data['title'] ?? $data['baslik'] ?? $data['a'] ?? null;
            $body = $data['body'] ?? $data['icerik'] ?? $data['content'] ?? $data['b'] ?? '';
            $category = $data['category'] ?? $data['kategori'] ?? null;

            if (! $title || trim($title) === '') {
                continue;
            }
            if ($validated['category_filter'] && $category && Str::lower($category) !== Str::lower($validated['category_filter'])) {
                continue;
            }

            if ($request->boolean('humanize') && $body) {
                $body = $ai->humanize($body);
            }

            Post::updateOrCreate(
                ['slug' => Str::slug($title).'-'.Str::random(4)],
                [
                    'title' => $title,
                    'body' => $body,
                    'category' => $category,
                    'meta_title' => $data['meta_title'] ?? $title,
                    'meta_description' => Str::limit(strip_tags($body), 160),
                    'status' => $validated['status'],
                    'ai_humanized' => $request->boolean('humanize'),
                    'published_at' => $validated['status'] === 'published' ? now() : null,
                ]
            );
            $count++;
        }
        fclose($handle);

        return redirect()->route('admin.blog.index')->with('success', "{$count} yazı içe aktarıldı.");
    }

    private function validated(Request $request, ?Post $post = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:120'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'status' => ['required', 'in:draft,published'],
            'published_at' => ['nullable', 'date'],
        ]);
    }

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = Str::slug($base) ?: 'yazi';
        $candidate = $slug;
        $i = 1;
        while (
            Post::query()
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $candidate)
                ->exists()
        ) {
            $candidate = $slug.'-'.$i++;
        }

        return $candidate;
    }
}
