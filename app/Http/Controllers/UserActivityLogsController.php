<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;
use Exception;

class UserActivityLogsController extends Controller
{

    // /**
    //  * Display a listing of the user activity logs.
    //  *
    //  * @return \Illuminate\View\View
    //  */
    public function index()
    {
        $userActivityLogs = UserActivityLog::with('user')->get();

        return view('user_activity_logs.index', compact('userActivityLogs'));
    }

    // /**
    //  * Show the form for creating a new user activity log.
    //  *
    //  * @return \Illuminate\View\View
    //  */
    public function create()
    {
        $users = User::pluck('name','id')->all();
        
        return view('user_activity_logs.create', compact('users'));
    }

    // /**
    //  * Store a new user activity log in the storage.
    //  *
    //  * @param Illuminate\Http\Request $request
    //  *
    //  * @return \Illuminate\Http\RedirectResponse | \Illuminate\Routing\Redirector
    //  */
    public function store(Request $request)
    {
        
        $data = $this->getData($request);
        
        $useract= UserActivityLog::create($data);

        return redirect()->route('user_activity_logs.user_activity_log.index')
            ->with('id', $useract->id);
    }

    // /**
    //  * Display the specified user activity log.
    //  *
    //  * @param int $id
    //  *
    //  * @return \Illuminate\View\View
    //  */
    public function show($id)
    {
        $userActivityLog = UserActivityLog::with('user')->findOrFail($id);

        return view('user_activity_logs.show', compact('userActivityLog'));
    }

    // /**
    //  * Show the form for editing the specified user activity log.
    //  *
    //  * @param int $id
    //  *
    //  * @return \Illuminate\View\View
    //  */
    public function edit($id)
    {
        $userActivityLog = UserActivityLog::findOrFail($id);
        $users = User::pluck('name','id')->all();

        return view('user_activity_logs.edit', compact('userActivityLog','users'));
    }

    // /**
    //  * Update the specified user activity log in the storage.
    //  *
    //  * @param int $id
    //  * @param Illuminate\Http\Request $request
    //  *
    //  * @return \Illuminate\Http\RedirectResponse | \Illuminate\Routing\Redirector
    //  */
    public function update($id, Request $request)
    {
        
        $data = $this->getData($request);
        
        $userActivityLog = UserActivityLog::findOrFail($id);
        $userActivityLog->update($data);

        return redirect()->route('user_activity_logs.user_activity_log.index')
            ->with('success_message', 'User Activity Log was successfully updated.');  
    }

    // /**
    //  * Remove the specified user activity log from the storage.
    //  *
    //  * @param int $id
    //  *
    //  * @return \Illuminate\Http\RedirectResponse | \Illuminate\Routing\Redirector
    //  */
    public function destroy($id)
    {
        try {
            $userActivityLog = UserActivityLog::findOrFail($id);
            $userActivityLog->delete();

            return redirect()->route('user_activity_logs.user_activity_log.index')
                ->with('success_message', 'User Activity Log was successfully deleted.');
        } catch (Exception $exception) {

            return back()->withInput()
                ->withErrors(['unexpected_error' => 'Unexpected error occurred while trying to process your request.']);
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
                'user_id' => 'required',
            'path' => 'required|string|min:1|max:255',
            'method' => 'required|string|min:1|max:10',
            'status_code' => 'required|numeric|min:-2147483648|max:2147483647',
            'ip_address' => 'required|string|min:1|max:15', 
        ];

        
        $data = $request->validate($rules);




        return $data;
    }

}
