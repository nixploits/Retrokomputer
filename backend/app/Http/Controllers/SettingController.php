<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Get active logo and login background configurations (Publicly accessible)
     */
    public function active()
    {
        $logoType = Setting::where('key', 'retro_logo_type')->first()?->value ?? 'text';
        $logoText = Setting::where('key', 'retro_logo_text')->first()?->value ?? 'Retro Komputer';
        $logoUrl = Setting::where('key', 'retro_logo_url')->first()?->value ?? '';
        $logoHeight = Setting::where('key', 'retro_logo_height')->first()?->value ?? '32';

        $bgType = Setting::where('key', 'login_background_type')->first()?->value ?? 'default';
        $bgColor = Setting::where('key', 'login_background_color')->first()?->value ?? '#f8fafc';
        $bgUrl = Setting::where('key', 'login_background_url')->first()?->value ?? '';

        return response()->json([
            'logo_type' => $logoType,
            'logo_text' => $logoText,
            'logo_url' => $logoUrl,
            'logo_height' => $logoHeight,
            'bg_type' => $bgType,
            'bg_color' => $bgColor,
            'bg_url' => $bgUrl,
        ]);
    }

    /**
     * Update configuration settings (Admin only)
     */
    public function update(Request $request)
    {
        // Require role validation to ensure only admin can customize settings
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Hanya admin yang dapat mengonfigurasi pengaturan.'], 403);
        }

        $request->validate([
            'logo_type' => 'nullable|string|in:text,image',
            'logo_text' => 'nullable|string|max:50',
            'logo_file' => 'nullable|image|max:2048', // max 2MB
            'logo_height' => 'nullable|integer|between:20,100', // allow 20px to 100px
            'bg_type' => 'nullable|string|in:default,color,image,grid',
            'bg_color' => 'nullable|string|max:20',
            'background_file' => 'nullable|image|max:4096', // max 4MB
        ]);

        // Handle Retro Logo
        if ($request->has('logo_type')) {
            Setting::updateOrCreate(['key' => 'retro_logo_type'], ['value' => $request->logo_type]);
        }
        if ($request->has('logo_text')) {
            Setting::updateOrCreate(['key' => 'retro_logo_text'], ['value' => $request->logo_text]);
        }
        if ($request->has('logo_height')) {
            Setting::updateOrCreate(['key' => 'retro_logo_height'], ['value' => $request->logo_height]);
        }
        if ($request->hasFile('logo_file')) {
            $path = $request->file('logo_file')->store('settings', 'public');
            Setting::updateOrCreate(['key' => 'retro_logo_url'], ['value' => '/storage/' . $path]);
            Setting::updateOrCreate(['key' => 'retro_logo_type'], ['value' => 'image']);
        }

        // Handle Login Background
        if ($request->has('bg_type')) {
            Setting::updateOrCreate(['key' => 'bg_type'], ['value' => $request->bg_type]); // sync deprecated key
            Setting::updateOrCreate(['key' => 'login_background_type'], ['value' => $request->bg_type]);
        }
        if ($request->has('bg_color')) {
            Setting::updateOrCreate(['key' => 'login_background_color'], ['value' => $request->bg_color]);
        }
        if ($request->hasFile('background_file')) {
            $path = $request->file('background_file')->store('settings', 'public');
            Setting::updateOrCreate(['key' => 'login_background_url'], ['value' => '/storage/' . $path]);
            Setting::updateOrCreate(['key' => 'login_background_type'], ['value' => 'image']);
        }

        return $this->active();
    }
}
