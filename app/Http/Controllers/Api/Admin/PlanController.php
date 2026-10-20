<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PlanRequest;
use App\Models\Plan;
use App\Models\Feature;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $plans = Plan::with('features')->latest(); // Eager load features
        if ($request->search) {
            $plans->where('title', 'like', '%' . $request->search . '%');
        }
        $plans = $plans->paginate(10);
        return response(['plans' => $plans]);
    }

    public function getAllPlans()
    {
        $plans = Plan::select('id', 'title')->get();
        return response(['plans' => $plans]);
    }

    public function getAllFeatures()
    {
        $features = Feature::all(['id', 'title']); // Only select needed columns
        return response(['features' => $features]);
    }

    public function edit(Plan $plan)
    {
        $plan->feature_ids = $plan->features()->pluck('features.id');
        return response(['plan' => $plan]);
    }

    public function store(PlanRequest $request)
    {
        $data = $request->validated();
        $featureIds = $data['feature_ids'] ?? [];
        unset($data['feature_ids']);

        if ($data['is_free']) {
            $data['price'] = 0;
        }

        $plan = Plan::create($data);
        $plan->features()->sync($featureIds);

        return response([
            'plan' => $plan->load('features'),
            'message' => __('messages.plan_created_successfully')
        ], 200);
    }

    public function update(PlanRequest $request, Plan $plan)
    {
        $data = $request->validated();
        $featureIds = $data['feature_ids'] ?? [];
        unset($data['feature_ids']);

        if ($data['is_free']) {
            $data['price'] = 0;
        }

        $plan->update($data);
        $plan->features()->sync($featureIds);

        return response([
            'plan' => $plan->load('features'),
            'message' => __('messages.plan_updated_successfully')
        ]);
    }

    public function delete(Plan $plan)
    {
        $plan->delete();
        return response(['message' => __('messages.plan_deleted_successfully')]);
    }
}