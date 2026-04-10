<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Facility;
use App\Models\SlotTimeRule;
use App\Models\Pricing;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    public function index()
    {
        $facilities = Facility::with('branch')->latest()->get();
        return view('facilities.index', compact('facilities'));
    }

    public function create()
    {
        $branches = Branch::where('status', 'active')->get();
        return view('facilities.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:football_field',
            'status' => 'required|in:active,maintenance,closed',
            'normal_price' => 'required|numeric|min:0',
            'slot_duration' => 'required|integer|min:15',
            'slot_interval' => 'required|integer|min:5',
            'earliest_start' => 'required|date_format:H:i',
            'latest_start' => 'required|date_format:H:i',
            'override_start.*' => 'nullable|date_format:H:i',
            'override_end.*' => 'nullable|date_format:H:i',
            'override_interval.*' => 'nullable|integer|min:5',
        ]);

        $overrides = $this->buildIntervalOverrides($request);

        $facility = Facility::create($request->only('branch_id', 'name', 'type', 'status'));

        SlotTimeRule::create([
            'facility_id' => $facility->id,
            'slot_duration' => $request->slot_duration,
            'slot_interval' => $request->slot_interval,
            'interval_overrides' => $overrides ?: null,
            'earliest_start' => $request->earliest_start,
            'latest_start' => $request->latest_start,
        ]);

        Pricing::create([
            'facility_id' => $facility->id,
            'normal_price' => $request->normal_price,
            'peak_price' => $request->normal_price,
        ]);

        ActivityLog::log('store', 'Facility', $facility->id, $facility->name);
        return redirect()->route('facilities.index')->with('success', 'Facility created successfully.');
    }

    public function show(Facility $facility)
    {
        return redirect()->route('facilities.edit', $facility);
    }

    public function edit(Facility $facility)
    {
        $branches = Branch::where('status', 'active')->get();
        $facility->load('slotTimeRule', 'pricings');
        return view('facilities.edit', compact('facility', 'branches'));
    }

    public function update(Request $request, Facility $facility)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:football_field',
            'status' => 'required|in:active,maintenance,closed',
            'normal_price' => 'required|numeric|min:0',
            'slot_duration' => 'required|integer|min:15',
            'slot_interval' => 'required|integer|min:5',
            'earliest_start' => 'required|date_format:H:i',
            'latest_start' => 'required|date_format:H:i',
            'override_start.*' => 'nullable|date_format:H:i',
            'override_end.*' => 'nullable|date_format:H:i',
            'override_interval.*' => 'nullable|integer|min:5',
        ]);

        $overrides = $this->buildIntervalOverrides($request);

        $facility->update($request->only('branch_id', 'name', 'type', 'status'));

        $facility->slotTimeRule()->updateOrCreate(
            ['facility_id' => $facility->id],
            array_merge(
                $request->only('slot_duration', 'slot_interval', 'earliest_start', 'latest_start'),
                ['interval_overrides' => $overrides ?: null]
            )
        );

        $pricing = $facility->pricings()->first();
        if ($pricing) {
            $pricing->update([
                'normal_price' => $request->normal_price,
            ]);
        } else {
            Pricing::create([
                'facility_id' => $facility->id,
                'normal_price' => $request->normal_price,
                'peak_price' => $request->normal_price,
            ]);
        }

        ActivityLog::log('update', 'Facility', $facility->id, $facility->name);
        return redirect()->route('facilities.index')->with('success', 'Facility updated successfully.');
    }

    public function destroy(Facility $facility)
    {
        ActivityLog::log('destroy', 'Facility', $facility->id, $facility->name);
        $facility->delete();
        return redirect()->route('facilities.index')->with('success', 'Facility deleted successfully.');
    }

    private function buildIntervalOverrides(Request $request)
    {
        $overrides = [];
        $starts = $request->input('override_start', []);
        $ends = $request->input('override_end', []);
        $intervals = $request->input('override_interval', []);

        foreach ($starts as $i => $start) {
            if ($start && ($ends[$i] ?? null) && ($intervals[$i] ?? null)) {
                $overrides[] = [
                    'start' => $start,
                    'end' => $ends[$i],
                    'interval' => (int) $intervals[$i],
                ];
            }
        }

        return $overrides;
    }
}
