<?php

namespace App\Http\Controllers\Api;

use App\Area;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Mockery\Exception;

class AreaController extends Controller
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
                $areas = Area::with('city')->whereIsActive(true)->get();
            } else if (Input::get('city_id')) {
                $areas = Area::whereCityId(Input::get('city_id'))->get();
            } else {
                $areas = Area::with('city')->paginate(15);
            }
            return response()->json($this->onSuccess($areas, "all areas"));
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
                'city_id' => 'required|exists:cities,id',
                'name' => 'required|string|max:255|unique:areas',
                'is_active' => 'required|boolean',
            ]);
            if ($validator->fails()) {
                return response()->json($this->onFailure($validator->errors(), 'validation errors'));
            } else {
                $area = Area::create($request->all());
                $data = $area;
                $data['city'] = $area->city;
                return response()->json($this->onSuccess($data, 'area created'));
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
            $area = Area::findOrFail($id);
            return response()->json($this->onSuccess($area,''));
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
                'city_id' => 'required|exists:cities,id',
                'name' => 'required|string|max:255|unique:areas,id,' . $id,
                'is_active' => 'required|boolean',
            ]);
            if ($validator->fails()) {
                return response()->json($this->onFailure($validator->errors(), 'validation errors'));
            } else {
                $area = Area::findOrFail($id);
                $area->fill($request->all());
                $area->save();
                return response()->json($this->onSuccess($area, 'area updated'));
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
            $area = Area::findOrFail($id);
            $area->delete();
            return response()->json($this->onSuccess($area, 'area deleted'));
        } catch (Exception $exception) {
            return response($this->onFailure(null, $exception->getMessage()));
        }
    }
}
