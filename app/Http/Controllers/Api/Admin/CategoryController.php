<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryStoreRequest;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::query();
        if ($request->search) {
            $categories = $categories->where('title', 'like', "%" . $request->search . "%");
        }
        $categories = $categories->latest()->paginate(10);
        return response(['categories' => $categories]);
    }

    public function store(CategoryStoreRequest $request)
    {
        $data = $request->all();

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/categories'), $filename);
            $data['logo'] = 'uploads/categories/' . $filename;
        }

        Category::create($data);
        return response(['message' => __('messages.category_created_successfully')], 200);
    }

    public function update(CategoryStoreRequest $request, Category $category)
    {
        $data = $request->all();

        if ($request->hasFile('logo')) {
            if ($category->logo && file_exists(public_path($category->logo))) {
                unlink(public_path($category->logo));
            }
            $file = $request->file('logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/categories'), $filename);
            $data['logo'] = 'uploads/categories/' . $filename;
        }
        
        $category->update($data);
        return response(['message' => __('messages.category_updated_successfully')]);
    }

    public function delete(Category $category)
    {
        if ($category->logo && file_exists(public_path($category->logo))) {
            unlink(public_path($category->logo));
        }
        $category->delete();
        return response(['message' => __('messages.category_deleted_successfully')]);
    }
}