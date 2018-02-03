<?php

namespace App\Http\Controllers\Api;

use App\City;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Mockery\Exception;

class CityController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware(['auth:api', 'role:admin'])->except(['index','show']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            if (Input::get('all')) {
                $cities = City::with('state')->whereIsActive(true)->get();
            } else if (Input::get('state_id')) {
                $cities = City::whereStateId(Input::get('state_id'))->get();
            }else {
                $cities = City::with('state')->paginate(15);
            }
            return response()->json($this->onSuccess($cities, "all cities"));
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
        try {
            $validator = Validator::make($request->all(), [
                'state_id' => 'required|exists:states,id',
                'name' => 'required|string|max:255|unique:cities',
                'is_active' => 'required|boolean',
            ]);
            if ($validator->fails()) {
                return response()->json($this->onFailure($validator->errors(), 'validation errors'));
            } else {
                $city = City::create($request->all());
                $data = $city;
                $data['state'] = $city->state;
                return response()->json($this->onSuccess($data, 'city created'));
            }
        } catch (Exception $exception) {
            return response($this->onFailure(null, $exception->getMessage()));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $city = City::findOrFail($id);
            return response()->json($this->onSuccess($city,''));
        } catch (Exception $exception) {
            return response($this->onFailure(null, $exception->getMessage()));
        }
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
            $validator = Validator::make($request->all(), [
                'state_id' => 'required|exists:states,id',
                'name' => 'required|string|max:255|unique:cities,id,'.$id,
                'is_active' => 'required|boolean',
            ]);
            if ($validator->fails()) {
                return response()->json($this->onFailure($validator->errors(), 'validation errors'));
            } else {
                $city = City::findOrFail($id);
                $city->fill($request->all());
                $city->save();
                return response()->json($this->onSuccess($city, 'city updated'));
            }
        } catch (Exception $exception) {
            return response($this->onFailure(null, $exception->getMessage()));
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
        try {
            $city = City::findOrFail($id);
            $city->delete();
            return response()->json($this->onSuccess($city, 'city deleted'));
        } catch (Exception $exception) {
            return response($this->onFailure(null, $exception->getMessage()));
        }
    }
}
