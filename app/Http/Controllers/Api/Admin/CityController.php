<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index(Request $request)
    {
        $cities = City::query();

        if ($request->search) {
            $cities = $cities->where('name_en', 'like', "%{$request->search}%")
                ->orWhere('name_fa', 'like', "%{$request->search}%");
        }

        $cities = $cities->latest()->paginate(20);

        return response(['cities' => $cities]);
    }

    public function getAll()
    {
        // Get all cities without pagination for dropdowns
        $cities = City::orderBy('name_en')->get();
        return response(['cities' => $cities]);
    }

    public function show(City $city)
    {
        return response(['city' => $city->load('banners')]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255|unique:cities',
            'name_fa' => 'required|string|max:255',
        ]);

        $validated['slug'] = \Illuminate\Support\Str::slug($validated['name_en']);

        $city = City::create($validated);

        return response(['message' => __('messages.city_created_successfully'), 'city' => $city], 201);
    }

    public function update(Request $request, City $city)
    {
        $validated = $request->validate([
            'name_en' => 'sometimes|required|string|max:255|unique:cities,name_en,' . $city->id,
            'name_fa' => 'sometimes|required|string|max:255',
        ]);

        if (isset($validated['name_en'])) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['name_en']);
        }

        $city->update($validated);

        return response(['message' => __('messages.city_updated_successfully'), 'city' => $city]);
    }

    public function destroy(City $city)
    {
        // Only delete if no banners are associated
        if ($city->banners()->count() > 0) {
            return response(['message' => __('messages.city_has_banners')], 422);
        }

        $city->delete();
        return response(['message' => __('messages.city_deleted_successfully')]);
    }
}
