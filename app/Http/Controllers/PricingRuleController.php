<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\PricingRule;
use App\Models\Branch;
use App\Models\Facility;
use Illuminate\Http\Request;

class PricingRuleController extends Controller
{
    public function index()
    {
        $pricingRules = PricingRule::with('facilities.branch')->latest()->get();
        return view('pricing-rules.index', compact('pricingRules'));
    }

    public function create()
    {
        $facilities = Facility::with('branch')
            ->where('status', 'active')
            ->whereHas('branch', fn($q) => $q->where('status', 'active'))
            ->get()
            ->groupBy('branch_id');
        $branches = Branch::where('status', 'active')->get()->keyBy('id');
        $existingRules = $this->getExistingRulesData();
        return view('pricing-rules.create', compact('facilities', 'branches', 'existingRules'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'days' => 'required|array|min:1',
            'days.*' => 'integer|between:0,6',
            'peak_price' => 'required|numeric|min:0',
            'peak_start' => 'required|date_format:H:i',
            'peak_end' => 'required|date_format:H:i',
            'facilities' => 'nullable|array',
            'facilities.*' => 'exists:facilities,id',
        ]);

        $days = $request->days;
        $facilityIds = $request->facilities ?? [];
        $allChecked = count($days) === 7;

        if (!empty($facilityIds)) {
            $conflicts = $this->findOverlaps($facilityIds, $days, $request->peak_start, $request->peak_end);
            if ($conflicts) {
                return back()->withInput()->with('error', 'Time overlap with existing rule: ' . $conflicts);
            }
        }

        if ($allChecked) {
            $rule = PricingRule::create([
                'name' => $request->name,
                'day_of_week' => null,
                'normal_price' => 0,
                'peak_price' => $request->peak_price,
                'peak_start' => $request->peak_start,
                'peak_end' => $request->peak_end,
            ]);
            $rule->facilities()->sync($request->facilities ?? []);
            ActivityLog::log('store', 'PricingRule', $rule->id, $rule->name);
        } else {
            $dayNames = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
            foreach ($days as $day) {
                $ruleName = count($days) === 1 ? $request->name : $request->name . ' (' . $dayNames[$day] . ')';
                $rule = PricingRule::create([
                    'name' => $ruleName,
                    'day_of_week' => $day,
                    'normal_price' => 0,
                    'peak_price' => $request->peak_price,
                    'peak_start' => $request->peak_start,
                    'peak_end' => $request->peak_end,
                ]);
                $rule->facilities()->sync($request->facilities ?? []);
                ActivityLog::log('store', 'PricingRule', $rule->id, $rule->name);
            }
        }

        return redirect()->route('pricing-rules.index')->with('success', 'Pricing rule created successfully.');
    }

    public function edit(PricingRule $pricingRule)
    {
        $facilities = Facility::with('branch')
            ->where('status', 'active')
            ->whereHas('branch', fn($q) => $q->where('status', 'active'))
            ->get()
            ->groupBy('branch_id');
        $branches = Branch::where('status', 'active')->get()->keyBy('id');
        $assignedFacilities = $pricingRule->facilities->pluck('id')->toArray();
        $existingRules = $this->getExistingRulesData();
        return view('pricing-rules.edit', compact('pricingRule', 'facilities', 'branches', 'assignedFacilities', 'existingRules'));
    }

    public function update(Request $request, PricingRule $pricingRule)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'day_of_week' => 'nullable|integer|between:0,6',
            'peak_price' => 'required|numeric|min:0',
            'peak_start' => 'required|date_format:H:i',
            'peak_end' => 'required|date_format:H:i',
            'facilities' => 'nullable|array',
            'facilities.*' => 'exists:facilities,id',
        ]);

        $facilityIds = $request->facilities ?? [];
        $days = $request->day_of_week !== null ? [(int) $request->day_of_week] : [0,1,2,3,4,5,6];

        if (!empty($facilityIds)) {
            $conflicts = $this->findOverlaps($facilityIds, $days, $request->peak_start, $request->peak_end, $pricingRule->id);
            if ($conflicts) {
                return back()->withInput()->with('error', 'Time overlap with existing rule: ' . $conflicts);
            }
        }

        $pricingRule->update([
            'name' => $request->name,
            'day_of_week' => $request->day_of_week,
            'normal_price' => 0,
            'peak_price' => $request->peak_price,
            'peak_start' => $request->peak_start,
            'peak_end' => $request->peak_end,
        ]);
        $pricingRule->facilities()->sync($request->facilities ?? []);

        ActivityLog::log('update', 'PricingRule', $pricingRule->id, $pricingRule->name);
        return redirect()->route('pricing-rules.index')->with('success', 'Pricing rule updated successfully.');
    }

    public function destroy(PricingRule $pricingRule)
    {
        ActivityLog::log('destroy', 'PricingRule', $pricingRule->id, $pricingRule->name);
        $pricingRule->delete();
        return redirect()->route('pricing-rules.index')->with('success', 'Pricing rule deleted successfully.');
    }

    private function findOverlaps(array $facilityIds, array $days, string $peakStart, string $peakEnd, ?int $excludeId = null)
    {
        $newStart = $this->timeToMinutes($peakStart);
        $newEnd = $this->timeToMinutes($peakEnd);
        if ($newEnd <= $newStart) $newEnd += 1440;

        $query = PricingRule::with('facilities:id')
            ->whereNotNull('peak_start')
            ->whereNotNull('peak_end');
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        $existing = $query->get();

        foreach ($existing as $rule) {
            $ruleFacilityIds = $rule->facilities->pluck('id')->toArray();
            $sharedFacilities = array_intersect($facilityIds, $ruleFacilityIds);
            if (empty($sharedFacilities)) continue;

            $daysMatch = false;
            foreach ($days as $day) {
                if ($rule->day_of_week === null || $rule->day_of_week === (int) $day) {
                    $daysMatch = true;
                    break;
                }
            }
            if (!$daysMatch) continue;

            $existStart = $this->timeToMinutes(substr($rule->peak_start, 0, 5));
            $existEnd = $this->timeToMinutes(substr($rule->peak_end, 0, 5));
            if ($existEnd <= $existStart) $existEnd += 1440;

            if ($newStart < $existEnd && $existStart < $newEnd) {
                return $rule->name;
            }
        }

        return null;
    }

    private function timeToMinutes(string $time): int
    {
        $parts = explode(':', $time);
        return (int) $parts[0] * 60 + (int) $parts[1];
    }

    private function getExistingRulesData()
    {
        return PricingRule::with('facilities:id')->get()->map(function ($rule) {
            return [
                'id' => $rule->id,
                'name' => $rule->name,
                'day_of_week' => $rule->day_of_week,
                'peak_start' => $rule->peak_start ? substr($rule->peak_start, 0, 5) : null,
                'peak_end' => $rule->peak_end ? substr($rule->peak_end, 0, 5) : null,
                'facility_ids' => $rule->facilities->pluck('id')->toArray(),
            ];
        })->values()->toArray();
    }
}
