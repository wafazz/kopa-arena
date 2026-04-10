<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Facility;
use App\Models\SlotTimeRule;
use App\Models\Pricing;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    private function branchId()
    {
        return auth()->user()->branch_id;
    }

    public function index()
    {
        $facilities = Facility::where('branch_id', $this->branchId())->latest()->get();
        return view('branch.facilities.index', compact('facilities'));
    }

    public function create()
    {
        return view('branch.facilities.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:football_field',
            'status' => 'required|in:active,maintenance,closed',
            'normal_price' => 'required|numeric|min:0',
            'peak_price' => 'nullable|numeric|min:0',
            'peak_start' => 'nullable|date_format:H:i',
            'peak_end' => 'nullable|date_format:H:i',
            'slot_duration' => 'required|integer|min:15',
            'slot_interval' => 'required|integer|min:5',
            'earliest_start' => 'required|date_format:H:i',
            'latest_start' => 'required|date_format:H:i',
            'override_start.*' => 'nullable|date_format:H:i',
            'override_end.*' => 'nullable|date_format:H:i',
            'override_interval.*' => 'nullable|integer|min:5',
        ]);

        $overrides = $this->buildIntervalOverrides($request);

        $facility = Facility::create([
            'branch_id' => $this->branchId(),
            'name' => $request->name,
            'type' => $request->type,
            'status' => $request->status,
        ]);

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
            'peak_price' => $request->peak_price ?? 0,
            'peak_start' => $request->peak_start,
            'peak_end' => $request->peak_end,
        ]);

        ActivityLog::log('store', 'Facility', $facility->id, $facility->name);
        return redirect()->route('branch.facilities.index')->with('success', 'Facility created successfully.');
    }

    public function edit(Facility $facility)
    {
        if ($facility->branch_id !== $this->branchId()) {
            abort(403);
        }
        $facility->load('slotTimeRule', 'pricings');
        return view('branch.facilities.edit', compact('facility'));
    }

    public function update(Request $request, Facility $facility)
    {
        if ($facility->branch_id !== $this->branchId()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:football_field',
            'status' => 'required|in:active,maintenance,closed',
            'normal_price' => 'required|numeric|min:0',
            'peak_price' => 'nullable|numeric|min:0',
            'peak_start' => 'nullable|date_format:H:i',
            'peak_end' => 'nullable|date_format:H:i',
            'slot_duration' => 'required|integer|min:30',
            'slot_interval' => 'required|integer|min:15',
            'earliest_start' => 'required|date_format:H:i',
            'latest_start' => 'required|date_format:H:i',
            'override_start.*' => 'nullable|date_format:H:i',
            'override_end.*' => 'nullable|date_format:H:i',
            'override_interval.*' => 'nullable|integer|min:5',
        ]);

        $overrides = $this->buildIntervalOverrides($request);

        $facility->update($request->only('name', 'type', 'status'));

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
                'peak_price' => $request->peak_price ?? 0,
                'peak_start' => $request->peak_start,
                'peak_end' => $request->peak_end,
            ]);
        } else {
            Pricing::create([
                'facility_id' => $facility->id,
                'normal_price' => $request->normal_price,
                'peak_price' => $request->peak_price ?? 0,
                'peak_start' => $request->peak_start,
                'peak_end' => $request->peak_end,
            ]);
        }

        ActivityLog::log('update', 'Facility', $facility->id, $facility->name);
        return redirect()->route('branch.facilities.index')->with('success', 'Facility updated successfully.');
    }

    public function destroy(Facility $facility)
    {
        if ($facility->branch_id !== $this->branchId()) {
            abort(403);
        }

        ActivityLog::log('destroy', 'Facility', $facility->id, $facility->name);
        $facility->delete();
        return redirect()->route('branch.facilities.index')->with('success', 'Facility deleted successfully.');
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
