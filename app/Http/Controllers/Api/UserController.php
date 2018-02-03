<?php

namespace App\Http\Controllers\Api;

use App\Role;
use App\Traits\ApiResponse;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Mockery\Exception;

class UserController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $users = User::with('roles')->paginate(10);
            return response()->json($this->onSuccess($users, 'all users'));
        } catch (Exception $exception) {
            return response()->json($this->onFailure(null, $exception->getMessage()));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            if ($request->has('role')) {
                $admin = Role::whereName('admin')->first();
                if ($request->get('status') == 'remove') {
                    $user->detachRole($admin);
                }
                if ($request->get('status') == 'add') {
                    $user->attachRole($admin);
                }
            }
            if ($request->has('is_active')) {
                $user->is_active = !$user->is_active;
                $user->save();
            }
            return response()->json($this->onSuccess($user, 'user updated'));
        } catch (Exception $exception) {
            return response()->json($this->onFailure(null, $exception->getMessage()));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
