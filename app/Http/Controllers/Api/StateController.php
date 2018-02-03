<?php

namespace App\Http\Controllers\Api;

use App\State;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Mockery\Exception;

class StateController extends Controller
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
                $states = State::with('country')->whereIsActive(true)->get();
            } else if (Input::get('country_id')) {
                $states = State::whereCountryId(Input::get('country_id'))->get();
            } else {
                $states = State::with('country')->paginate(15);
            }
            return response()->json($this->onSuccess($states, "all states"));
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
                'country_id' => 'required|exists:countries,id',
                'name' => 'required|string|max:255|unique:states',
                'is_active' => 'required|boolean',
            ]);
            if ($validator->fails()) {
                return response()->json($this->onFailure($validator->errors(), 'validation errors'));
            } else {
                $state = State::create($request->all());
                $data = $state;
                $data['country'] = $state->country;
                return response()->json($this->onSuccess($data, 'state created'));
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
            $validator = Validator::make($request->all(), [
                'country_id' => 'required|exists:countries,id',
                'name' => 'required|string|max:255|unique:states,id,'.$id,
                'is_active' => 'required|boolean',
            ]);
            if ($validator->fails()) {
                return response()->json($this->onFailure($validator->errors(), 'validation errors'));
            } else {
                $state = State::findOrFail($id);
                $state->fill($request->all());
                $state->save();
                return response()->json($this->onSuccess($state, 'state updated'));
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
            $state = State::findOrFail($id);
            $state->delete();
            return response()->json($this->onSuccess($state, 'state deleted'));

        } catch (Exception $exception) {
            return response($this->onFailure(null, $exception->getMessage()));
        }
    }
}
