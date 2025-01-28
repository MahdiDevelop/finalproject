<?php
namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        return UserResource::collection(User::all());
    }

    // public function show(User $user)
    // {
    //     return new UserResource($user);
    // }

    // public function update(Request $request, User $user)
    // {
    //     $request->validate([
    //         'password' => 'nullable|string|min:6',
    //     ]);

    //     $user->update($request->except('password'));

    //     if ($request->filled('password')) {
    //         $user->password = bcrypt($request->password);
    //         $user->save();
    //     }

    //     return new UserResource($user);
    // }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'username' => 'required',
    //         'email' => 'required|email',
    //         'password' => 'required|string|min:6',
    //     ]);

    //     $user = User::create([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'password' => bcrypt($request->password),
    //     ]);

    //     return new UserResource($user);
    // }
}
