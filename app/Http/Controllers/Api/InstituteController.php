<?php

namespace App\Http\Controllers\Api;

use App\Institute;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Mockery\Exception;

class InstituteController extends Controller
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
            $institutes = Institute::with(['address','institutePics'])->paginate(10);
            return response()->json($this->onSuccess($institutes, 'all institutes'));
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
        try {
            $institutes = Institute::where('name','like','%'.$name.'%')->with(['address','institutePics'])->paginate(10);
            return response()->json($this->onSuccess($institutes, 'all institutes'));
        } catch (Exception $exception) {
            return response()->json($this->onFailure(null, $exception->getMessage()));
        }
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
        try{
            $institute = Institute::findOrFail($id);
            if($request->get('rating')){
                $institute->rating = $request->get('rating');
            }
            if($request->get('is_active')){
                $institute->is_active = !$institute->is_active;
            }
            $institute->save();
            return response()->json($this->onSuccess($institute,'updated'));
        }catch (Exception $exception){
            return response()->json($this->onFailure(null,$exception->getMessage()));
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
        try{
            $institute = Institute::findOrFail($id);
            $institute->delete();
            return response()->json($this->onSuccess($institute, 'delted institute'));
        }catch(Exception $exception){
            return response()->json($this->onFailure(null,$exception->getMessage()));
        }
    }
}
