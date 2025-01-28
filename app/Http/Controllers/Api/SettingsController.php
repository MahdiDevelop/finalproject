<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\SettingsResource;
use App\Models\Settings;
class SettingsController extends Controller
{
    public function index()
    {
        $query = Settings::query();
        // if ($request->has('delete')) {
        //     $query->where('existense', $request->input('delete'));
        // }
        return SettingsResource::collection(Settings::all());
    }
}
