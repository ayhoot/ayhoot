<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use Illuminate\Http\Request;

class HeroSlideController extends Controller
{
    public function index()
    {
        $slides = HeroSlide::orderBy('display_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return response([
            'slides' => $slides,
        ]);
    }

    public function show(HeroSlide $heroSlide)
    {
        return response([
            'slide' => $heroSlide,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'button_text' => 'nullable|string|max:100',
            'button_link' => 'nullable|string|max:255',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'image_file' => 'nullable|image|max:10240',
            'image' => 'nullable|string|max:1000',
        ]);

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destination = public_path('uploads/hero_slides');
            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }
            $file->move($destination, $filename);
            $validated['image'] = '/uploads/hero_slides/' . $filename;
        }

        unset($validated['image_file']);

        if (!isset($validated['display_order'])) {
            $maxOrder = HeroSlide::max('display_order') ?? 0;
            $validated['display_order'] = $maxOrder + 1;
        }

        if (!isset($validated['is_active'])) {
            $validated['is_active'] = true;
        }

        $slide = HeroSlide::create($validated);

        return response([
            'message' => 'Hero slide created successfully',
            'slide' => $slide,
        ], 201);
    }

    public function update(Request $request, HeroSlide $heroSlide)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'button_text' => 'nullable|string|max:100',
            'button_link' => 'nullable|string|max:255',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'image_file' => 'nullable|image|max:10240',
            'image' => 'nullable|string|max:1000',
        ]);

        if ($request->hasFile('image_file')) {
            // Delete old uploaded image if present
            if ($heroSlide->image && str_starts_with($heroSlide->image, '/uploads/hero_slides/')) {
                $oldPath = public_path(ltrim($heroSlide->image, '/'));
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $file = $request->file('image_file');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destination = public_path('uploads/hero_slides');
            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }
            $file->move($destination, $filename);
            $validated['image'] = '/uploads/hero_slides/' . $filename;
        }

        unset($validated['image_file']);

        $heroSlide->update($validated);

        return response([
            'message' => 'Hero slide updated successfully',
            'slide' => $heroSlide,
        ]);
    }

    public function destroy(HeroSlide $heroSlide)
    {
        if ($heroSlide->image && str_starts_with($heroSlide->image, '/uploads/hero_slides/')) {
            $oldPath = public_path(ltrim($heroSlide->image, '/'));
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        $heroSlide->delete();

        return response([
            'message' => 'Hero slide deleted successfully',
        ]);
    }

    public function toggleActive(HeroSlide $heroSlide)
    {
        $heroSlide->is_active = !$heroSlide->is_active;
        $heroSlide->save();

        return response([
            'message' => 'Slide status updated',
            'slide' => $heroSlide,
        ]);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|exists:hero_slides,id',
            'orders.*.display_order' => 'required|integer|min:0',
        ]);

        foreach ($request->orders as $item) {
            HeroSlide::where('id', $item['id'])->update(['display_order' => $item['display_order']]);
        }

        return response([
            'message' => 'Slides reordered successfully',
        ]);
    }
}
