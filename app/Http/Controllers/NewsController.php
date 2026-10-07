<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\View\View;

class NewsController extends Controller
{
    // Halaman depan: berita terbaru
    public function home(): View
    {
        $news = News::published()->with('images')->latest()->take(7)->get();

        return view('news.home', [
            'featured' => $news->first(),
            'others' => $news->slice(1),
        ]);
    }

    // Daftar semua berita terbit
    public function index(): View
    {
        $news = News::published()->with('images')->latest()->paginate(9);

        return view('news.index', compact('news'));
    }

    // Baca satu berita (draft hanya bisa dibuka admin sebagai pratinjau)
    public function show(News $news): View
    {
        abort_unless(
            $news->status === 'published' || auth()->user()?->isAdmin(),
            404
        );

        $others = News::published()
            ->with('images')
            ->where('id', '!=', $news->id)
            ->latest()
            ->take(3)
            ->get();

        return view('news.show', compact('news', 'others'));
    }
}