<?php

namespace App\Http\Controllers\Api;

use App\Models\Accounts;
use App\Http\Resources\AccountsResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\UserController;

class AccountController extends UserController
{
    public function index(Request $request)
    {
        return AccountsResource::collection(Accounts::all());
        }

    
}