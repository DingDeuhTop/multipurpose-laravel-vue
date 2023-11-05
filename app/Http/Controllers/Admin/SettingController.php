<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return Settings::pluck('value', 'key')->toArray();
    }

    public function update()
    {
        $settings = request()->all();

        foreach ($settings as $key => $value) {
            Settings::where('key', $key)->update(['value' => $value]);
        }

        return response()->json(['success' => true]);
    }
}
