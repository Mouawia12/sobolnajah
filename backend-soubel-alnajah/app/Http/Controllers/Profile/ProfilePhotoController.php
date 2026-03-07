<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfilePhotoRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class ProfilePhotoController extends Controller
{
    public function update(UpdateProfilePhotoRequest $request): RedirectResponse
    {
        $user = $request->user();
        $newPath = $request->file('profile_photo')->store('profile-photos', 'public');
        $oldPath = $user->profile_photo_path;

        $user->forceFill([
            'profile_photo_path' => $newPath,
        ])->save();

        if ($oldPath && $oldPath !== $newPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return back()->with('success', __('تم تحديث الصورة الشخصية بنجاح.'));
    }
}
