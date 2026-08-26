<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            // Delete old avatar if it is a local file
            if ($user->avatar && !str_starts_with($user->avatar, 'http')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
            // Save new avatar file
            $path = $request->file('avatar')->store('profile', 'public');
            // Crop it center-squared on backend
            $this->cropImageToSquare($path);
            $data['avatar'] = $path;
        } elseif ($request->has('avatar') && is_string($request->input('avatar'))) {
            // Delete old avatar if it is a local file
            if ($user->avatar && !str_starts_with($user->avatar, 'http')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
        }

        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Helper to crop uploaded images into a center-aligned square.
     */
    private function cropImageToSquare($filePath)
    {
        $realPath = storage_path('app/public/' . $filePath);
        if (!file_exists($realPath)) return;

        $info = getimagesize($realPath);
        if (!$info) return;

        $mime = $info['mime'];
        switch ($mime) {
            case 'image/jpeg':
                $src = imagecreatefromjpeg($realPath);
                break;
            case 'image/png':
                $src = imagecreatefrompng($realPath);
                break;
            case 'image/webp':
                $src = imagecreatefromwebp($realPath);
                break;
            default:
                return;
        }

        if (!$src) return;

        $width = imagesx($src);
        $height = imagesy($src);
        $size = min($width, $height);

        $dst = imagecreatetruecolor($size, $size);

        // Keep transparency for png/webp
        if ($mime === 'image/png' || $mime === 'image/webp') {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        }

        $x = ($width - $size) / 2;
        $y = ($height - $size) / 2;

        imagecopyresampled($dst, $src, 0, 0, $x, $y, $size, $size, $size, $size);

        switch ($mime) {
            case 'image/jpeg':
                imagejpeg($dst, $realPath, 90);
                break;
            case 'image/png':
                imagepng($dst, $realPath, 9);
                break;
            case 'image/webp':
                imagewebp($dst, $realPath, 90);
                break;
        }

        imagedestroy($src);
        imagedestroy($dst);
    }
}
