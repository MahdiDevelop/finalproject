<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Models\user;
use Illuminate\Http\Request;
use Exception;

class UsersController extends Controller
{

    // /**
    //  * Display a listing of the users.
    //  *
    //  * @return \Illuminate\View\View
    //  */
    public function index()
    {
        $users = user::get();
        return response($users);
    }

    // /**
    //  * Show the form for creating a new user.
    //  *
    //  * @return \Illuminate\View\View
    //  */
    public function create()
    {
        
        
        return view('users.create');
    }

    // /**
    //  * Store a new user in the storage.
    //  *
    //  * @param Illuminate\Http\Request $request
    //  *
    //  * @return \Illuminate\Http\RedirectResponse | \Illuminate\Routing\Redirector
    //  */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'nullable|string|email|unique:users',
            'password' => 'required|string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'category' => $request->category,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['id' => $user->id]);
    }

    // /**
    //  * Display the specified user.
    //  *
    //  * @param int $id
    //  *
    //  * @return \Illuminate\View\View
    //  */
    public function show($id)
    {
        $user = user::findOrFail($id);

        return response($user);
        
    }

    // /**
    //  * Show the form for editing the specified user.
    //  *
    //  * @param int $id
    //  *
    //  * @return \Illuminate\View\View
    //  */
    public function edit($id)
    {
        $user = user::findOrFail($id);
        

        return view('users.edit', compact('user'));
    }

    // /**
    //  * Update the specified user in the storage.
    //  *
    //  * @param int $id
    //  * @param Illuminate\Http\Request $request
    //  *
    //  * @return \Illuminate\Http\RedirectResponse | \Illuminate\Routing\Redirector
    //  */
    public function update($id, Request $request)
{
    $request->validate([
        'name' => 'required|string',
        'email' => 'nullable|string|email|unique:users,email,' . $id,
        'password' => 'nullable|string',
    ]);

    $data = $this->getData($request);

    if ($request->has('password')) {
        $data['password'] = Hash::make($request->password);
    }

    $user = User::findOrFail($id);
    $user->update($data);

    return response()->json([ 'message' => 'User updated successfully']);
}


    // /**
    //  * Remove the specified user from the storage.
    //  *
    //  * @param int $id
    //  *
    //  * @return \Illuminate\Http\RedirectResponse | \Illuminate\Routing\Redirector
    //  */
    public function destroy($id)
    {
        try {
            $user = user::findOrFail($id);
            $user->delete();

            return response()->json([ 'message' => 'User deleted successfully']);
        } catch (Exception $exception) {

            return response()->json([ 'message' => 'User not deleted successfully']);
        }
    }

    
    // /**
    //  * Get the request's data from the request.
    //  *
    //  * @param Illuminate\Http\Request\Request $request 
    //  * @return array
    //  */
    protected function getData(Request $request)
    {
        $rules = [
                'name' => 'nullable|string|min:0|max:40',
            'password' => 'nullable|string|min:0|max:80',
            'category' => 'nullable|string|min:0|max:15', 
        ];

        
        $data = $request->validate($rules);




        return $data;
    }

}
