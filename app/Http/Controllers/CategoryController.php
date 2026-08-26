<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        // Mengambil semua data kategori dari database, diurutkan dari yang terbaru
        $categories = Category::latest()->get();
        return view('kategori.index', compact('categories'));
    }

    public function create()
    {
        // Menampilkan halaman form tambah kategori
        return view('kategori.create');
    }

    public function store(Request $request)
    {
        // Validasi inputan
        $request->validate([
            'category_name' => 'required|string|max:255'
        ]);

        // Simpan ke database
        Category::create([
            'category_name' => $request->category_name
        ]);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil ditambahkan!');
    }

    public function edit(Category $kategori)
    {
        // Menampilkan halaman edit 
        return view('kategori.edit', compact('kategori'));
    }

    public function update(Request $request, Category $kategori)
    {
        $request->validate([
            'category_name' => 'required|string|max:255'
        ]);

        $kategori->update([
            'category_name' => $request->category_name
        ]);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil diperbarui!');
    }

    public function destroy(Category $kategori)
    {
        $kategori->delete();
        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil dihapus!');
    }
}