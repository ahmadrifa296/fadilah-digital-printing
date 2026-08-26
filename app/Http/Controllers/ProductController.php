<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->latest()->get();
        return view('produk.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('produk.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $calcType = $request->input('calculation_type', 'fixed');
        $isSizeBased = in_array($calcType, ['custom_size', 'quantity_custom_size']);

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'product_type' => 'required|string|in:custom,ready',
            'calculation_type' => 'required|string|in:fixed,quantity,custom_size,quantity_custom_size',
            'product_name' => 'required|string|max:255',
            'base_price' => $isSizeBased ? 'nullable|numeric|min:0' : 'required|numeric|min:0',
            'price_per_square_meter' => $isSizeBased ? 'required|numeric|min:0' : 'nullable|numeric|min:0',
            'stock' => $isSizeBased ? 'nullable|numeric|min:0' : 'required|numeric|min:0',
            'minimum_order' => 'nullable|integer|min:1',
            'maximum_order' => 'nullable|integer|min:1',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'discount_percent' => 'nullable|integer|min:0|max:100',
            'discount_flat' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'weight' => 'required|integer|min:1',
            'length' => 'required|integer|min:1',
            'width' => 'required|integer|min:1',
            'height' => 'required|integer|min:1',
            'min_width' => $isSizeBased ? 'required|numeric|min:0.01' : 'nullable|numeric|min:0.01',
            'max_width' => $isSizeBased ? 'required|numeric|min:0.01' : 'nullable|numeric|min:0.01',
            'min_length' => $isSizeBased ? 'required|numeric|min:0.01' : 'nullable|numeric|min:0.01',
            'max_length' => $isSizeBased ? 'required|numeric|min:0.01' : 'nullable|numeric|min:0.01',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'images_meta' => 'nullable|string',
            'variants' => 'nullable|array',
            'variants.*.type' => 'required|string|in:ukuran,bahan,finishing',
            'variants.*.name' => 'required|string|max:255',
            'variants.*.price_modifier' => 'required|numeric',
            'variants.*.stock' => 'required|integer|min:0',
            'requires_design_file' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $stock = $isSizeBased ? 99999 : $request->stock;

            $product = Product::create([
                'category_id' => $request->category_id,
                'product_type' => $request->product_type,
                'calculation_type' => $calcType,
                'product_name' => $request->product_name,
                'base_price' => $request->base_price ?: 0,
                'price_per_square_meter' => $isSizeBased ? $request->price_per_square_meter : 0,
                'stock' => $stock,
                'minimum_order' => $request->minimum_order ?: 1,
                'maximum_order' => $request->maximum_order ?: ($isSizeBased ? 99999 : 100),
                'sku' => $request->sku ?: 'PROD-' . time(),
                'discount_percent' => $request->discount_percent ?: 0,
                'discount_flat' => $request->discount_flat ?: 0,
                'description' => $request->description,
                'weight' => $request->weight,
                'length' => $request->length,
                'width' => $request->width,
                'height' => $request->height,
                'min_width' => $isSizeBased ? $request->min_width : 0.1,
                'max_width' => $isSizeBased ? $request->max_width : 100.0,
                'min_length' => $isSizeBased ? $request->min_length : 0.1,
                'max_length' => $isSizeBased ? $request->max_length : 100.0,
                'meta_title' => $request->meta_title ?: $request->product_name,
                'meta_description' => $request->meta_description ?: strip_tags($request->description),
                'requires_design_file' => $request->has('requires_design_file'),
            ]);

            // Simpan gambar dari metadata drag and drop
            $files = $request->file('gallery') ?: [];
            $meta = json_decode($request->input('images_meta', '[]'), true);
            $primaryPath = null;

            foreach ($meta as $order => $item) {
                if ($item['type'] === 'new') {
                    $index = $item['index'];
                    if (isset($files[$index])) {
                        $path = $files[$index]->store('products/gallery', 'public');
                        $isPrimary = filter_var($item['is_primary'], FILTER_VALIDATE_BOOLEAN);

                        ProductImage::create([
                            'product_id' => $product->id,
                            'image_path' => '/storage/' . $path,
                            'is_primary' => $isPrimary,
                            'sort_order' => $order,
                        ]);

                        if ($isPrimary) {
                            $primaryPath = '/storage/' . $path;
                        }
                    }
                }
            }

            // Fallback: Jika tidak ada primary dari meta tetapi ada gambar yang di-upload, set yang pertama sebagai primary
            if (!$primaryPath && !empty($product->images)) {
                $firstImg = $product->images()->first();
                if ($firstImg) {
                    $firstImg->update(['is_primary' => true]);
                    $primaryPath = $firstImg->image_path;
                }
            }

            if ($primaryPath) {
                $product->update(['image' => $primaryPath]);
            }

            // Simpan variasi (untuk Custom Printing, semua calculation_type)
            if ($request->product_type === 'custom' && $request->has('variants')) {
                foreach ($request->variants as $variantData) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'variant_type' => $variantData['type'],
                        'variant_name' => $variantData['name'],
                        'price_modifier' => $variantData['price_modifier'],
                        'stock' => $variantData['stock'],
                    ]);
                }
            }

            // Simpan Log Aktivitas
            ActivityLog::create([
                'user_id' => Auth::id(),
                'activity' => 'Tambah Produk',
                'description' => "Admin menambah produk baru: {$product->product_name} dengan harga Rp " . number_format($product->price),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Kirim Notifikasi ke Semua Customer
            $customers = \App\Models\User::where('role', 'customer')->get();
            $hasDiscount = ($product->discount_percent > 0 || $product->discount_flat > 0);
            foreach ($customers as $c) {
                $c->notify(new \App\Notifications\AppNotification(
                    'Produk Baru!',
                    "Produk {$product->product_name} kini tersedia di Fadilah Printing.",
                    'tag',
                    'orange',
                    route('produk.show', $product->id),
                    'produk'
                ));

                if ($hasDiscount) {
                    $promoText = $product->discount_percent > 0 ? "Diskon {$product->discount_percent}%" : "Diskon Rp " . number_format($product->discount_flat, 0, ',', '.');
                    $c->notify(new \App\Notifications\AppNotification(
                        'Promo Spesial!',
                        "Produk {$product->product_name} sedang promo {$promoText}!",
                        'percent',
                        'red',
                        route('produk.show', $product->id),
                        'promo'
                    ));
                }
            }

            DB::commit();
            return redirect()->route('produk.index')->with('success', 'Produk dan variasi berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan produk: ' . $e->getMessage());
        }
    }

    public function edit(Product $produk)
    {
        $categories = Category::all();
        $produk->load(['images', 'variants']);
        
        $variantsPayload = $produk->variants->map(function ($v) {
            return [
                'type' => $v->variant_type,
                'name' => $v->variant_name,
                'price_modifier' => (int) $v->price_modifier,
                'stock' => (int) $v->stock,
            ];
        })->toArray();

        $imagesPayload = $produk->images->map(function ($img) {
            return [
                'id' => $img->id,
                'type' => 'existing',
                'url' => asset($img->image_path),
                'is_primary' => $img->is_primary,
            ];
        })->toArray();

        return view('produk.edit', compact('produk', 'categories', 'variantsPayload', 'imagesPayload'));
    }

    public function update(Request $request, Product $produk)
    {
        $calcType = $request->input('calculation_type', $produk->calculation_type ?? 'fixed');
        $isSizeBased = in_array($calcType, ['custom_size', 'quantity_custom_size']);

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'product_type' => 'required|string|in:custom,ready',
            'calculation_type' => 'required|string|in:fixed,quantity,custom_size,quantity_custom_size',
            'product_name' => 'required|string|max:255',
            'base_price' => $isSizeBased ? 'nullable|numeric|min:0' : 'required|numeric|min:0',
            'price_per_square_meter' => $isSizeBased ? 'required|numeric|min:0' : 'nullable|numeric|min:0',
            'stock' => $isSizeBased ? 'nullable|numeric|min:0' : 'required|numeric|min:0',
            'minimum_order' => 'nullable|integer|min:1',
            'maximum_order' => 'nullable|integer|min:1',
            'sku' => 'sometimes|nullable|string|max:100|unique:products,sku,' . $produk->id,
            'discount_percent' => 'sometimes|nullable|integer|min:0|max:100',
            'discount_flat' => 'sometimes|nullable|numeric|min:0',
            'description' => 'nullable|string',
            'weight' => 'required|integer|min:1',
            'length' => 'required|integer|min:1',
            'width' => 'required|integer|min:1',
            'height' => 'required|integer|min:1',
            'min_width' => $isSizeBased ? 'required|numeric|min:0.01' : 'nullable|numeric|min:0.01',
            'max_width' => $isSizeBased ? 'required|numeric|min:0.01' : 'nullable|numeric|min:0.01',
            'min_length' => $isSizeBased ? 'required|numeric|min:0.01' : 'nullable|numeric|min:0.01',
            'max_length' => $isSizeBased ? 'required|numeric|min:0.01' : 'nullable|numeric|min:0.01',
            'meta_title' => 'sometimes|nullable|string|max:255',
            'meta_description' => 'sometimes|nullable|string',
            'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'images_meta' => 'nullable|string',
            'variants' => 'sometimes|nullable|array',
            'variants.*.type' => 'required|string|in:ukuran,bahan,finishing',
            'variants.*.name' => 'required|string|max:255',
            'variants.*.price_modifier' => 'required|numeric',
            'variants.*.stock' => 'required|integer|min:0',
            'requires_design_file' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $stock = $isSizeBased ? 99999 : $request->stock;

            $produk->update([
                'category_id' => $request->category_id,
                'product_type' => $request->product_type,
                'calculation_type' => $calcType,
                'product_name' => $request->product_name,
                'base_price' => $request->base_price ?: 0,
                'price_per_square_meter' => $isSizeBased ? $request->price_per_square_meter : 0,
                'stock' => $stock,
                'minimum_order' => $request->minimum_order ?: 1,
                'maximum_order' => $request->maximum_order ?: ($isSizeBased ? 99999 : 100),
                'sku' => $request->has('sku') ? ($request->sku ?: $produk->sku) : $produk->sku,
                'discount_percent' => $request->has('discount_percent') ? ($request->discount_percent ?: 0) : $produk->discount_percent,
                'discount_flat' => $request->has('discount_flat') ? ($request->discount_flat ?: 0) : $produk->discount_flat,
                'description' => $request->description,
                'weight' => $request->weight,
                'length' => $request->length,
                'width' => $request->width,
                'height' => $request->height,
                'min_width' => $isSizeBased ? $request->min_width : 0.1,
                'max_width' => $isSizeBased ? $request->max_width : 100.0,
                'min_length' => $isSizeBased ? $request->min_length : 0.1,
                'max_length' => $isSizeBased ? $request->max_length : 100.0,
                'meta_title' => $request->has('meta_title') ? ($request->meta_title ?: $request->product_name) : $produk->meta_title,
                'meta_description' => $request->has('meta_description') ? ($request->meta_description ?: strip_tags($request->description)) : $produk->meta_description,
                'requires_design_file' => $request->has('requires_design_file'),
            ]);

            // Simpan / Sinkronisasi gambar dari metadata drag and drop
            $files = $request->file('gallery') ?: [];
            $meta = json_decode($request->input('images_meta', '[]'), true);
            $retainedIds = [];
            $primaryPath = null;

            foreach ($meta as $order => $item) {
                if ($item['type'] === 'existing') {
                    $retainedIds[] = $item['id'];
                    $isPrimary = filter_var($item['is_primary'], FILTER_VALIDATE_BOOLEAN);

                    $img = ProductImage::findOrFail($item['id']);
                    $img->update([
                        'is_primary' => $isPrimary,
                        'sort_order' => $order,
                    ]);

                    if ($isPrimary) {
                        $primaryPath = $img->image_path;
                    }
                } elseif ($item['type'] === 'new') {
                    $index = $item['index'];
                    if (isset($files[$index])) {
                        $path = $files[$index]->store('products/gallery', 'public');
                        $isPrimary = filter_var($item['is_primary'], FILTER_VALIDATE_BOOLEAN);

                        $img = ProductImage::create([
                            'product_id' => $produk->id,
                            'image_path' => '/storage/' . $path,
                            'is_primary' => $isPrimary,
                            'sort_order' => $order,
                        ]);

                        if ($isPrimary) {
                            $primaryPath = '/storage/' . $path;
                        }
                    }
                }
            }

            // Hapus gambar yang tidak dipertahankan dari database dan storage
            $imagesToDelete = $produk->images()->whereNotIn('id', $retainedIds)->get();
            foreach ($imagesToDelete as $img) {
                if ($img->image_path && !str_contains($img->image_path, 'http')) {
                    $oldPath = str_replace('/storage/', '', $img->image_path);
                    Storage::disk('public')->delete($oldPath);
                }
                $img->delete();
            }

            // Fallback: Jika tidak ada primary dari meta tetapi ada gambar tersisa, set yang pertama sebagai primary
            if (!$primaryPath) {
                $firstImg = $produk->images()->orderBy('sort_order', 'asc')->first();
                if ($firstImg) {
                    $firstImg->update(['is_primary' => true]);
                    $primaryPath = $firstImg->image_path;
                }
            }

            if ($primaryPath) {
                $produk->update(['image' => $primaryPath]);
            } else {
                $produk->update(['image' => null]);
            }

            // Update variasi: untuk Custom Printing
            if ($request->product_type === 'custom') {
                if ($request->has('variants')) {
                    $produk->variants()->delete();
                    $variantsPayload = $request->input('variants') ?: [];
                    foreach ($variantsPayload as $variantData) {
                        ProductVariant::create([
                            'product_id' => $produk->id,
                            'variant_type' => $variantData['type'],
                            'variant_name' => $variantData['name'],
                            'price_modifier' => $variantData['price_modifier'],
                            'stock' => $variantData['stock'],
                        ]);
                    }
                }
            } else {
                // Jika diubah menjadi ready stock, hapus semua variasi lamanya
                $produk->variants()->delete();
            }

            // Simpan Log Aktivitas
            ActivityLog::create([
                'user_id' => Auth::id(),
                'activity' => 'Edit Produk',
                'description' => "Admin memperbarui informasi produk: {$produk->product_name}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Kirim notifikasi jika promo baru di-set
            $oldDiscountPercent = $produk->getOriginal('discount_percent');
            $oldDiscountFlat = $produk->getOriginal('discount_flat');
            $hadNoDiscount = ($oldDiscountPercent <= 0 && $oldDiscountFlat <= 0);
            $hasDiscount = ($produk->discount_percent > 0 || $produk->discount_flat > 0);
            if ($hadNoDiscount && $hasDiscount) {
                $customers = \App\Models\User::where('role', 'customer')->get();
                $promoText = $produk->discount_percent > 0 ? "Diskon {$produk->discount_percent}%" : "Diskon Rp " . number_format($produk->discount_flat, 0, ',', '.');
                foreach ($customers as $c) {
                    $c->notify(new \App\Notifications\AppNotification(
                        'Promo Spesial!',
                        "Produk {$produk->product_name} sedang promo {$promoText}!",
                        'percent',
                        'red',
                        route('produk.show', $produk->id),
                        'promo'
                    ));
                }
            }

            DB::commit();
            return redirect()->route('produk.index')->with('success', 'Produk berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui produk: ' . $e->getMessage());
        }
    }

    public function destroy(Product $produk)
    {
        DB::beginTransaction();
        try {
            // Hapus semua gambar dari storage
            foreach ($produk->images as $img) {
                if ($img->image_path && !str_contains($img->image_path, 'http')) {
                    $p = str_replace('/storage/', '', $img->image_path);
                    Storage::disk('public')->delete($p);
                }
            }

            // Hapus file utama jika ada dan terpisah
            if ($produk->image && !str_contains($produk->image, 'http')) {
                $pMain = str_replace('/storage/', '', $produk->image);
                Storage::disk('public')->delete($pMain);
            }

            $name = $produk->product_name;
            $produk->delete(); // cascading delete di handle database migration

            ActivityLog::create([
                'user_id' => Auth::id(),
                'activity' => 'Hapus Produk',
                'description' => "Admin menghapus produk: {$name}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();
            return redirect()->route('produk.index')->with('success', 'Produk berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus produk: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan detail produk secara interaktif untuk pelanggan.
     */
    public function show($id)
    {
        $produk = Product::with(['category', 'images', 'variants', 'reviews.user'])
            ->findOrFail($id);

        // Ambil ulasan yang diizinkan untuk ditampilkan
        $visibleReviews = $produk->reviews()->where('is_visible', true)->with('user')->latest()->get();

        // Map data review untuk frontend Alpine.js
        $reviewsPayload = $visibleReviews->map(function($r) {
            return [
                'id' => $r->id,
                'rating' => (int)$r->rating,
                'comment' => $r->comment,
                'photo' => $r->photo ? asset($r->photo) : null,
                'reply' => $r->reply,
                'created_at_human' => $r->created_at->diffForHumans(),
                'user' => [
                    'name' => $r->user->name,
                    'avatar_url' => $r->user->avatar_url,
                    'initials' => strtoupper(substr($r->user->name, 0, 2))
                ]
            ];
        });

        return view('produk.show', compact('produk', 'visibleReviews', 'reviewsPayload'));
    }

    /**
     * Hapus gambar galeri secara spesifik (Ajax).
     */
    public function destroyImage(Request $request, ProductImage $image)
    {
        if ($image->image_path && !str_contains($image->image_path, 'http')) {
            Storage::disk('public')->delete($image->image_path);
        }

        $image->delete();

        return response()->json(['success' => true]);
    }
}