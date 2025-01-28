<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Report;
use App\Http\Resources\ReportResource;
class ReportController extends Controller
{
    public function index()
    {
        $query = Report::query();
        // if ($request->has('delete')) {
        //     $query->where('existense', $request->input('delete'));
        // }
        return ReportResource::collection(Report::all());
    }

}
