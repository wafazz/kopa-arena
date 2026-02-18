<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $logo = Setting::get('logo');
        $logoWhite = Setting::get('logo_white');
        return view('settings.index', compact('logo', 'logoWhite'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'logo_white' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $filename);

            // Delete old uploaded logo
            $old = Setting::get('logo');
            if ($old && file_exists(public_path($old))) {
                unlink(public_path($old));
            }

            Setting::set('logo', 'uploads/' . $filename);
        }

        if ($request->hasFile('logo_white')) {
            $file = $request->file('logo_white');
            $filename = 'logo_white_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $filename);

            $old = Setting::get('logo_white');
            if ($old && file_exists(public_path($old))) {
                unlink(public_path($old));
            }

            Setting::set('logo_white', 'uploads/' . $filename);
        }

        // SenangPay + OnSend text fields
        $textFields = [
            'senangpay_mode',
            'senangpay_sandbox_merchant_id',
            'senangpay_sandbox_secret_key',
            'senangpay_production_merchant_id',
            'senangpay_production_secret_key',
            'onsend_api_token',
        ];
        foreach ($textFields as $key) {
            if ($request->has($key)) {
                Setting::set($key, $request->input($key));
            }
        }

        ActivityLog::log('update', 'Setting', null, 'Settings updated');
        return back()->with('success', 'Settings updated successfully.');
    }
}
