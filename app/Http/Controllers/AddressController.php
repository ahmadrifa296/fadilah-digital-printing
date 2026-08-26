<?php

namespace App\Http\Controllers;

use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AddressController extends Controller
{
    public function index()
    {
        $addresses = Auth::user()->addresses()->orderBy('is_default', 'desc')->latest()->get();
        return view('addresses.index', compact('addresses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:100',
            'receiver_name' => 'required|string|max:255',
            'phone' => ['required', 'string', 'regex:/^(?:\+62|62|0)8[1-9][0-9]{6,11}$/'], // Validasi format HP Indonesia
            'province' => 'required|string',
            'city' => 'required|string',
            'district' => 'required|string',
            'subdistrict' => 'required|string',
            'postal_code' => 'required|numeric|digits:5', // 5 digit
            'rt' => 'required|string|max:10',
            'rw' => 'required|string|max:10',
            'no_rumah' => 'required|string|max:50',
            'patokan' => 'nullable|string|max:255',
            'full_address' => 'required|string|min:20', // Alamat minimal 20 karakter
            'notes' => 'nullable|string|max:255',
        ], [
            'phone.regex' => 'Format nomor HP tidak valid. Gunakan format Indonesia (contoh: 08123456789).',
            'postal_code.numeric' => 'Kode pos harus berupa angka.',
            'postal_code.digits' => 'Kode pos harus terdiri dari 5 digit.',
            'full_address.min' => 'Alamat lengkap minimal harus 20 karakter.',
        ]);

        $address = DB::transaction(function () use ($request) {
            $user = Auth::user();

            $isDefault = $request->has('is_default') || $user->addresses()->count() === 0;

            if ($isDefault) {
                // Set all other addresses default status to false
                $user->addresses()->update(['is_default' => false]);
            }

            return $user->addresses()->create([
                'label' => $request->label,
                'receiver_name' => $request->receiver_name,
                'phone' => $request->phone,
                'province' => $request->province,
                'city' => $request->city,
                'district' => $request->district,
                'subdistrict' => $request->subdistrict,
                'postal_code' => $request->postal_code,
                'rt' => $request->rt,
                'rw' => $request->rw,
                'no_rumah' => $request->no_rumah,
                'patokan' => $request->patokan,
                'full_address' => $request->full_address,
                'notes' => $request->notes,
                'is_default' => $isDefault,
            ]);
        });

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Alamat berhasil ditambahkan!',
                'addresses' => Auth::user()->addresses()->orderBy('is_default', 'desc')->latest()->get()
            ]);
        }

        return redirect()->route('addresses.index')->with('success', 'Alamat berhasil ditambahkan!');
    }

    public function update(Request $request, UserAddress $address)
    {
        // Authorization boundary check
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'label' => 'required|string|max:100',
            'receiver_name' => 'required|string|max:255',
            'phone' => ['required', 'string', 'regex:/^(?:\+62|62|0)8[1-9][0-9]{6,11}$/'],
            'province' => 'required|string',
            'city' => 'required|string',
            'district' => 'required|string',
            'subdistrict' => 'required|string',
            'postal_code' => 'required|numeric|digits:5',
            'rt' => 'required|string|max:10',
            'rw' => 'required|string|max:10',
            'no_rumah' => 'required|string|max:50',
            'patokan' => 'nullable|string|max:255',
            'full_address' => 'required|string|min:20',
            'notes' => 'nullable|string|max:255',
        ], [
            'phone.regex' => 'Format nomor HP tidak valid. Gunakan format Indonesia (contoh: 08123456789).',
            'postal_code.numeric' => 'Kode pos harus berupa angka.',
            'postal_code.digits' => 'Kode pos harus terdiri dari 5 digit.',
            'full_address.min' => 'Alamat lengkap minimal harus 20 karakter.',
        ]);

        DB::transaction(function () use ($request, $address) {
            $user = Auth::user();

            $isDefault = $request->has('is_default') || $request->is_default == 1;

            if ($isDefault) {
                $user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            }

            $address->update([
                'label' => $request->label,
                'receiver_name' => $request->receiver_name,
                'phone' => $request->phone,
                'province' => $request->province,
                'city' => $request->city,
                'district' => $request->district,
                'subdistrict' => $request->subdistrict,
                'postal_code' => $request->postal_code,
                'rt' => $request->rt,
                'rw' => $request->rw,
                'no_rumah' => $request->no_rumah,
                'patokan' => $request->patokan,
                'full_address' => $request->full_address,
                'notes' => $request->notes,
                'is_default' => $isDefault,
            ]);
        });

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Alamat berhasil diperbarui!',
                'addresses' => Auth::user()->addresses()->orderBy('is_default', 'desc')->latest()->get()
            ]);
        }

        return redirect()->route('addresses.index')->with('success', 'Alamat berhasil diperbarui!');
    }

    public function destroy(Request $request, UserAddress $address)
    {
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        DB::transaction(function () use ($address) {
            $wasDefault = $address->is_default;
            $address->delete();

            // If the deleted address was default, make another one default
            if ($wasDefault) {
                $nextAddress = Auth::user()->addresses()->first();
                if ($nextAddress) {
                    $nextAddress->update(['is_default' => true]);
                }
            }
        });

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Alamat berhasil dihapus!',
                'addresses' => Auth::user()->addresses()->orderBy('is_default', 'desc')->latest()->get()
            ]);
        }

        return redirect()->route('addresses.index')->with('success', 'Alamat berhasil dihapus!');
    }

    public function makeDefault(Request $request, UserAddress $address)
    {
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        DB::transaction(function () use ($address) {
            Auth::user()->addresses()->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Alamat utama berhasil diubah!',
                'addresses' => Auth::user()->addresses()->orderBy('is_default', 'desc')->latest()->get()
            ]);
        }

        return redirect()->route('addresses.index')->with('success', 'Alamat utama berhasil diubah!');
    }

    // =========================================================================
    // API WILAYAH INDONESIA PROXY
    // =========================================================================

    public function getProvinces()
    {
        try {
            $response = Http::get('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json');
            return response()->json($response->json() ?? []);
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }

    public function getRegencies($province_id)
    {
        try {
            $response = Http::get("https://www.emsifa.com/api-wilayah-indonesia/api/regencies/{$province_id}.json");
            return response()->json($response->json() ?? []);
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }

    public function getDistricts($regency_id)
    {
        try {
            $response = Http::get("https://www.emsifa.com/api-wilayah-indonesia/api/districts/{$regency_id}.json");
            return response()->json($response->json() ?? []);
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }

    public function getVillages($district_id)
    {
        try {
            $response = Http::get("https://www.emsifa.com/api-wilayah-indonesia/api/villages/{$district_id}.json");
            return response()->json($response->json() ?? []);
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }
}
