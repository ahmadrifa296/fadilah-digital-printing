<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\CmsBanner;
use App\Models\CmsFeedback;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;

class CmsController extends Controller
{
    /**
     * Tampilan Setting Global Website & SEO Default.
     */
    public function indexSettings()
    {
        $settings = Setting::all()->pluck('value', 'key');
        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update Setting Global Website & SEO Default.
     */
    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'web_name' => 'required|string|max:255',
            'web_phone' => 'required|string|max:20',
            'web_email' => 'required|email|max:255',
            'web_address' => 'required|string',
            'web_about' => 'required|string',
            'web_faq' => 'nullable|string', // JSON format string
            'seo_title' => 'required|string|max:255',
            'seo_description' => 'required|string',
            'seo_keywords' => 'required|string',
            'company_logo' => 'nullable|image|mimes:jpg,jpeg,png,svg,webp|max:2048',
            
            // Warehouse (Gudang Asal) Biteship Settings
            'warehouse_name' => 'required|string|max:255',
            'warehouse_address' => 'required|string',
            'warehouse_province' => 'required|string',
            'warehouse_city' => 'required|string',
            'warehouse_district' => 'required|string',
            'warehouse_subdistrict' => 'required|string',
            'warehouse_postal_code' => 'required|numeric|digits:5',
            'warehouse_latitude' => 'nullable|numeric',
            'warehouse_longitude' => 'nullable|numeric',
            'warehouse_biteship_origin_id' => 'nullable|string|max:100',
        ]);

        // Process file upload if company_logo file is attached
        if ($request->hasFile('company_logo')) {
            $oldLogo = Setting::getVal('company_logo');
            if ($oldLogo) {
                $oldPath = str_replace('/storage/', '', $oldLogo);
                Storage::disk('public')->delete($oldPath);
            }

            $file = $request->file('company_logo');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('settings', $filename, 'public');
            
            $logoUrl = '/storage/' . $path;
            
            // Set value in both key-value store and company_logo column
            Setting::updateOrCreate(
                ['key' => 'company_logo'],
                [
                    'value' => $logoUrl,
                    'company_logo' => $logoUrl,
                    'description' => 'Logo Perusahaan'
                ]
            );
        }

        // Save other textual settings
        foreach ($data as $key => $value) {
            if ($key !== 'company_logo') {
                Setting::setVal($key, $value);
            }
        }

        // Simpan Log Aktivitas
        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Mengupdate Pengaturan Website',
            'description' => 'Admin memperbarui konfigurasi situs global dan setelan SEO.',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->back()->with('success', 'Pengaturan website berhasil diperbarui!');
    }

    /**
     * Hapus Logo Perusahaan dari storage dan database.
     */
    public function deleteLogo(Request $request)
    {
        $oldLogo = Setting::getVal('company_logo');
        if ($oldLogo) {
            $oldPath = str_replace('/storage/', '', $oldLogo);
            Storage::disk('public')->delete($oldPath);
        }

        // Set database values to null/delete
        Setting::updateOrCreate(
            ['key' => 'company_logo'],
            [
                'value' => null,
                'company_logo' => null
            ]
        );

        // Simpan Log Aktivitas
        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Menghapus Logo Perusahaan',
            'description' => 'Admin menghapus logo resmi perusahaan dari setelan situs.',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->back()->with('success', 'Logo perusahaan berhasil dihapus!');
    }

    /**
     * Kelola Banner / Slider.
     */
    public function indexBanners()
    {
        $banners = CmsBanner::latest()->get();
        return view('admin.banners.index', compact('banners'));
    }

    public function storeBanner(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'link' => 'nullable|string|max:255',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('banners', 'public');
        }

        CmsBanner::create([
            'title' => $request->title,
            'image' => $imagePath ? '/storage/' . $imagePath : '',
            'link' => $request->link,
            'is_active' => true,
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Membuat Banner Baru',
            'description' => "Admin mengunggah banner slider baru dengan judul: {$request->title}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->back()->with('success', 'Banner berhasil ditambahkan!');
    }

    public function destroyBanner(CmsBanner $banner)
    {
        // Hapus file gambar jika lokal
        if (str_contains($banner->image, '/storage/banners/')) {
            $path = str_replace('/storage/', '', $banner->image);
            Storage::disk('public')->delete($path);
        }

        $title = $banner->title;
        $banner->delete();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Menghapus Banner',
            'description' => "Admin menghapus banner slider: {$title}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->back()->with('success', 'Banner berhasil dihapus!');
    }



    /**
     * Kelola Customer Feedbacks.
     */
    public function indexFeedbacks()
    {
        $feedbacks = CmsFeedback::latest()->get();
        return view('admin.feedbacks.index', compact('feedbacks'));
    }

    public function readFeedback(CmsFeedback $feedback)
    {
        $feedback->update(['is_read' => true]);
        return redirect()->back()->with('success', 'Pesan ditandai sebagai dibaca.');
    }

    public function destroyFeedback(CmsFeedback $feedback)
    {
        $feedback->delete();
        return redirect()->back()->with('success', 'Pesan berhasil dihapus.');
    }

    /**
     * Simpan Feedback dari Form Kontak Publik (Halaman Depan).
     */
    public function storeFeedback(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        CmsFeedback::create([
            'name' => $request->name,
            'email' => $request->email,
            'subject' => $request->subject,
            'message' => $request->message,
        ]);

        return redirect()->back()->with('success', 'Pesan Anda berhasil terkirim! Terima kasih atas feedback-nya.');
    }

    /**
     * Jalankan backup database dan download filenya (Admin Only).
     */
    public function downloadBackup(Request $request)
    {
        // Jalankan artisan command
        Artisan::call('db:backup');

        // Cari file backup terbaru
        $files = Storage::disk('local')->files('backups');
        if (empty($files)) {
            return redirect()->back()->with('error', 'Gagal memicu backup database.');
        }

        // Urutkan file berdasarkan waktu modifikasi terbaru
        usort($files, function($a, $b) {
            return Storage::disk('local')->lastModified($b) - Storage::disk('local')->lastModified($a);
        });

        $latestFile = $files[0];
        $path = storage_path('app/' . $latestFile);

        // Catat aktivitas backup
        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity' => 'Backup Database',
            'description' => "Admin mengunduh salinan backup database SQL.",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->download($path);
    }

    /**
     * Tampilkan riwayat aktivitas sistem (Audit Logs) untuk admin.
     */
    public function indexLogs()
    {
        $logs = ActivityLog::with('user')
            ->latest()
            ->paginate(50);

        return view('admin.logs.index', compact('logs'));
    }
}
