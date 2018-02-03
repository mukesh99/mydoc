<?php

namespace App\Http\Controllers\Api;

use App\Country;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Mockery\Exception;

class CountryController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware(['auth:api', 'role:admin'])->except('index');
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
                $countries = Country::whereIsActive(true)->get();
            } else {
                $countries = Country::paginate(15);
            }
            return response()->json($this->onSuccess($countries, "all countries"));
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
                'name' => 'required|string|max:255|unique:countries',
                'is_active' => 'required|boolean',
            ]);
            if ($validator->fails()) {
                return response()->json($this->onFailure($validator->errors(), 'validation errors'));
            } else {
                $country = Country::create($request->all());
                return response()->json($this->onSuccess($country, 'country created'));
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
                'name' => 'required|string|max:255|unique:countries,id,' . $id,
                'is_active' => 'required|boolean',
            ]);
            if ($validator->fails()) {
                return response()->json($this->onFailure($validator->errors(), 'validation errors'));
            } else {
                $country = Country::findOrFail($id);
                $country->fill($request->all());
                $country->save();
                return response()->json($this->onSuccess($country, 'country updated'));
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
            $country = Country::findOrFail($id);
            $country->delete();
            return response()->json($this->onSuccess($country, 'country deleted'));

        } catch (Exception $exception) {
            return response($this->onFailure(null, $exception->getMessage()));
        }
    }
}
