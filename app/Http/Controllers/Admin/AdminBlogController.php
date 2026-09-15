<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\AiContentService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminBlogController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->category))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.blog.index', compact('posts'));
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
}
