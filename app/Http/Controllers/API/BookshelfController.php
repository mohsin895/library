<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Bookshelf;
use App\Helpers\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class BookshelfController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dataList = Bookshelf::orderBy('id','desc')->get();
        return response()->json($dataList);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
    
        DB::beginTransaction();
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|regex:/^[A-Za-z]/',
                'location' => 'required',
               
              
            ]);
    
         
              
            $dataInfo = new Bookshelf($validatedData);
    
                if ($dataInfo->save()) {
                   
    
                    DB::commit();
                    $msg = 'Data Insert Successfully.';
                    return Response::successResponse($msg, $dataInfo);
                } else {
                    DB::rollBack();
                    $msg = 'Something went wrong. Please try again.';
                    return Response::failedResponse($msg, $dataInfo);
                }
          
        } catch (Exception $err) {

            DB::rollBack();
            $msg = 'Something went wrong. Please try again.';
            return Response::errorResponse($err, $msg);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Bookshelf $bookshelf) {
        return $bookshelf;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bookshelf $bookshelf)
    {
        DB::beginTransaction();
        try {
            // Validate the incoming request
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|regex:/^[A-Za-z]/', // Name must be alphabetic
                'location' => 'required', // Location is required
            ]);
          
            // Update the existing bookshelf instance with validated data
            $bookshelf->update($validatedData);
    
            DB::commit();
    
            // Respond with success message and updated data
            $msg = 'Data updated successfully.';
            return Response::successResponse($msg, $bookshelf);
        } catch (Exception $err) {
            DB::rollBack();
    
            // Handle error and return response
            $msg = 'Something went wrong. Please try again.';
            return Response::errorResponse($err, $msg);
        }
    }
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bookshelf $bookshelf)
    {
        try {
            DB::beginTransaction();
    
            // No need to find the bookshelf again, it's already passed as a model
            if (!$bookshelf) {
                return response()->json([
                    'message' => 'Data not found!',
                    'error' => true
                ], 404);
            }
    
            // Attempt to delete the bookshelf
            if ($bookshelf->delete()) {
                DB::commit();
                return response()->json([
                    'message' => 'Data deleted successfully.',
                    'error' => false
                ], 200);
            } else {
                DB::rollBack();
                return response()->json([
                    'message' => 'Data deletion failed. Please try again!',
                    'error' => true
                ], 500);
            }
    
        } catch (\Exception $err) {
            DB::rollBack();
            return response()->json([
                'message' => 'Something went wrong. Please try again.',
                'error' => true,
                'errorDetails' => [
                    'message' => $err->getMessage(),
                    'trace' => $err->getTraceAsString(),
                ],
            ], 500);
        }
    }
    
}
