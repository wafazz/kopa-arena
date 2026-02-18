<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\PricingRule;
use App\Models\Branch;
use Illuminate\Http\Request;

class PricingRuleController extends Controller
{
    public function index()
    {
        $pricingRules = PricingRule::with('branches')->latest()->get();
        return view('pricing-rules.index', compact('pricingRules'));
    }

    public function create()
    {
        $branches = Branch::where('status', 'active')->get();
        return view('pricing-rules.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'day_of_week' => 'nullable|integer|between:0,6',
            'normal_price' => 'required|numeric|min:0',
            'peak_price' => 'nullable|numeric|min:0',
            'peak_start' => 'nullable|date_format:H:i',
            'peak_end' => 'nullable|date_format:H:i',
            'branches' => 'nullable|array',
            'branches.*' => 'exists:branches,id',
        ]);

        $rule = PricingRule::create($request->only('name', 'day_of_week', 'normal_price', 'peak_price', 'peak_start', 'peak_end'));
        $rule->branches()->sync($request->branches ?? []);

        ActivityLog::log('store', 'PricingRule', $rule->id, $rule->name);
        return redirect()->route('pricing-rules.index')->with('success', 'Pricing rule created successfully.');
    }

    public function edit(PricingRule $pricingRule)
    {
        $branches = Branch::where('status', 'active')->get();
        $assignedBranches = $pricingRule->branches->pluck('id')->toArray();
        return view('pricing-rules.edit', compact('pricingRule', 'branches', 'assignedBranches'));
    }

    public function update(Request $request, PricingRule $pricingRule)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'day_of_week' => 'nullable|integer|between:0,6',
            'normal_price' => 'required|numeric|min:0',
            'peak_price' => 'nullable|numeric|min:0',
            'peak_start' => 'nullable|date_format:H:i',
            'peak_end' => 'nullable|date_format:H:i',
            'branches' => 'nullable|array',
            'branches.*' => 'exists:branches,id',
        ]);

        $pricingRule->update($request->only('name', 'day_of_week', 'normal_price', 'peak_price', 'peak_start', 'peak_end'));
        $pricingRule->branches()->sync($request->branches ?? []);

        ActivityLog::log('update', 'PricingRule', $pricingRule->id, $pricingRule->name);
        return redirect()->route('pricing-rules.index')->with('success', 'Pricing rule updated successfully.');
    }

    public function destroy(PricingRule $pricingRule)
    {
        ActivityLog::log('destroy', 'PricingRule', $pricingRule->id, $pricingRule->name);
        $pricingRule->delete();
        return redirect()->route('pricing-rules.index')->with('success', 'Pricing rule deleted successfully.');
    }
}
