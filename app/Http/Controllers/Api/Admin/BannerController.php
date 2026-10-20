<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\City;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        $banners = Banner::with('city')
            ->when($request->user_type, function ($query) use ($request) {
                $query->where('user_type', $request->user_type);
            })
            ->when($request->has('city_id') && $request->city_id !== '' && $request->city_id !== null, function ($query) use ($request) {
                if ($request->city_id === 'all' || $request->city_id === 'null') {
                    $query->whereNull('city_id');
                } else {
                    $query->where('city_id', $request->city_id);
                }
            })
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('title', 'like', "%{$request->search}%")
                        ->orWhere('description', 'like', "%{$request->search}%");
                });
            })
            ->orderBy('display_order')
            ->paginate(15);

        return response(['banners' => $banners]);
    }

    public function show(Banner $banner)
    {
        return response(['banner' => $banner->load('city')]);
    }

    public function store(Request $request)
    {
        if ($request->has('city_id') && ($request->city_id === 'all' || $request->city_id === '' || $request->city_id === 'null')) {
            $request->merge(['city_id' => null]);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'city_id' => 'nullable|exists:cities,id',
            'user_type' => 'required|in:student,teacher,institute',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'image_url' => 'nullable|url',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/banners'), $filename);
            $validated['image_url'] = asset('uploads/banners/' . $filename);
        }

        $banner = Banner::create($validated);

        return response(['message' => __('messages.banner_created_successfully'), 'banner' => $banner->load('city')], 201);
    }

    public function update(Request $request, Banner $banner)
    {
        if ($request->has('city_id') && ($request->city_id === 'all' || $request->city_id === '' || $request->city_id === 'null')) {
            $request->merge(['city_id' => null]);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'city_id' => 'nullable|exists:cities,id',
            'user_type' => 'sometimes|required|in:student,teacher,institute',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'image_url' => 'nullable|url',
            'is_active' => 'sometimes|boolean',
            'display_order' => 'sometimes|integer|min:0',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($banner->image_url && strpos($banner->image_url, 'uploads/banners') !== false) {
                $oldImagePath = str_replace(asset('uploads/banners/'), '', $banner->image_url);
                $oldImageFullPath = public_path('uploads/banners/' . $oldImagePath);
                if (file_exists($oldImageFullPath)) {
                    unlink($oldImageFullPath);
                }
            }
            
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/banners'), $filename);
            $validated['image_url'] = asset('uploads/banners/' . $filename);
        }

        $banner->update($validated);

        return response(['message' => __('messages.banner_updated_successfully'), 'banner' => $banner->load('city')]);
    }

    public function destroy(Banner $banner)
    {
        $banner->delete();
        return response(['message' => __('messages.banner_deleted_successfully')]);
    }

    public function toggleActive(Banner $banner)
    {
        $banner->update(['is_active' => !$banner->is_active]);
        return response(['message' => __('messages.banner_updated_successfully'), 'banner' => $banner]);
    }
}
