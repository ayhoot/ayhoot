<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChangePasswordRequest;
use App\Http\Requests\Admin\SettingUpdateBannerRequest;
use App\Http\Requests\Admin\SettingUpdateDiscountRequest;
use App\Models\Setting;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::firstOrCreate([]);
        return response($settings);
    }

    public function updateBanner(SettingUpdateBannerRequest $request)
    {
        $settings = Setting::first();
        $newBannerData = $settings->banner_settings ?? [];

        $newBannerData['title'] = $request->input('banner_title');
        $newBannerData['description'] = $request->input('banner_description');
        $newBannerData['ctaTitle'] = $request->input('banner_cta_title');
        $newBannerData['ctaLink'] = $request->input('banner_cta_link');

        if ($request->hasFile('banner_image')) {
            $destinationPath = public_path('uploads/banners');
            if (isset($newBannerData['imageUrl']) && File::exists(public_path($newBannerData['imageUrl']))) {
                File::delete(public_path($newBannerData['imageUrl']));
            }
            $file = $request->file('banner_image');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move($destinationPath, $fileName);
            $newBannerData['imageUrl'] = 'uploads/banners/' . $fileName;
        }
        
        $settings->banner_settings = $newBannerData;
        $settings->save();
        
        return response([
            'message' => __('messages.banner_settings_updated_successfully'),
            'settings' => $settings->fresh(),
        ]);
    }

    public function updateDiscount(Request $request)
    {
        $settings = Setting::first();
        $currentDiscountData = $request->input('currentDiscount');
        $editingIndex = $request->input('editingIndex');
        $existingDiscounts = $request->input('discounts', []);

        if ($editingIndex !== null && $editingIndex >= 0 && !empty($currentDiscountData)) {
            $existingDiscounts[$editingIndex] = [
                'id' => $existingDiscounts[$editingIndex]['id'] ?? uniqid(),
                'percentage' => $currentDiscountData['percentage'],
                'code' => $currentDiscountData['code'],
                'startDate' => $currentDiscountData['startDate'],
                'endDate' => $currentDiscountData['endDate'],
                'plans' => $currentDiscountData['plans'] ?? [],
                'courses' => isset($currentDiscountData['courses']) && !empty($currentDiscountData['courses']),
                'exams' => isset($currentDiscountData['exams']) && !empty($currentDiscountData['exams']),
            ];
        } elseif (!empty($currentDiscountData) && isset($currentDiscountData['percentage'])) {
            $newDiscount = [
                'id' => uniqid(),
                'percentage' => $currentDiscountData['percentage'],
                'code' => $currentDiscountData['code'],
                'startDate' => $currentDiscountData['startDate'],
                'endDate' => $currentDiscountData['endDate'],
                'plans' => $currentDiscountData['plans'] ?? [],
                'courses' => isset($currentDiscountData['courses']) && !empty($currentDiscountData['courses']),
                'exams' => isset($currentDiscountData['exams']) && !empty($currentDiscountData['exams']),
            ];
            $existingDiscounts[] = $newDiscount;
        }
        
        $settings->discount_settings = $existingDiscounts;
        $settings->save();
        
        return response([
            'message' => __('messages.discount_settings_updated_successfully'),
            'settings' => $settings->fresh(),
        ]);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $admin = Admin::find(auth('admin')->user()->id);
        if (!Hash::check($request->current, $admin->password)) {
            return response(['message' => __('messages.current_password_does_not_match')], 422);
        }
        $admin->password = Hash::make($request->new);
        $admin->save();
        return response(['message' => __('messages.password_changed_successfully')]);
    }

    /**
     * Update locale settings
     */
    public function updateLocaleSettings(Request $request)
    {
        $request->validate([
            'locale_filtering_enabled' => 'required|boolean',
            'supported_locales' => 'required|array',
            'supported_locales.*' => 'required|string|size:2',
            'default_locale' => 'required|string|size:2',
        ]);

        $generalSetting = Setting::where('key', 'general_config')->first();
        
        if (!$generalSetting) {
            return response(['message' => 'General settings not found'], 404);
        }

        $generalSettings = $generalSetting->general_settings ?? [];
        
        // Update locale settings
        $generalSettings['locale_filtering_enabled'] = $request->locale_filtering_enabled;
        $generalSettings['supported_locales'] = $request->supported_locales;
        $generalSettings['default_locale'] = $request->default_locale;
        
        $generalSetting->update([
            'general_settings' => $generalSettings
        ]);

        return response([
            'message' => __('messages.locale_settings_updated_successfully'),
            'settings' => $generalSetting->fresh(),
        ]);
    }

    /**
     * Get locale settings
     */
    public function getLocaleSettings()
    {
        $generalSetting = Setting::where('key', 'general_config')->first();
        $generalSettings = $generalSetting->general_settings ?? [];
        
        return response([
            'locale_filtering_enabled' => $generalSettings['locale_filtering_enabled'] ?? false,
            'supported_locales' => $generalSettings['supported_locales'] ?? ['en'],
            'default_locale' => $generalSettings['default_locale'] ?? 'en',
        ]);
    }
}