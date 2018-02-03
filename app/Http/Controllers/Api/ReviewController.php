<?php

namespace App\Http\Controllers\Api;

use App\Review;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Mockery\Exception;

class ReviewController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware(['auth:api', 'role:admin'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $reviews = Review::where('institute_id', '=', Input::get('institute_id'))->paginate(5);
            if (Input::get('admin')) {
                $reviews = Review::with('institute')->paginate(15);
            }
            return response()->json($this->onSuccess($reviews, 'all reviews'));
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
                'institute_id' => 'required|exists:institutes,id',
                'rating' => 'required|digits:1',
                'description' => 'required|string|max:255',
            ]);
            if ($validator->fails()) {
                return response()->json($this->onFailure($validator->errors(), 'validation errors'));
            } else {
                $review = Review::create($request->all());
                return response()->json($this->onSuccess($review, 'review created'));
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
        //
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
            $review = Review::findOrFail($id);
            $review->delete();
            return response()->json($this->onSuccess(null,'deleted'));
        }catch (Exception $exception){
            return response($this->onFailure(null, $exception->getMessage()));
        }
    }
}
