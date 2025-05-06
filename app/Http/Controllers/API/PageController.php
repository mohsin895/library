<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Response;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\Page;
use Exception;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Chapter $chapter)
    {
        $dataList = $chapter->pages()->orderBy('id', 'desc')->get();
        return response()->json($dataList);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
    
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'chapter_id' => 'required|exists:chapters,id',  
                'content' => 'required',
                'page_number' => 'required|integer',
            ]);
    
            // Find the bookshelf the book will belong to
            $chapter = Chapter::findOrFail($validatedData['chapter_id']);
    
            // Create the new book and associate it with the bookshelf
            $page = new Page([
                'content' => $validatedData['content'],
                'page_number' => $validatedData['page_number'],
              
            ]);
    
           
             $chapter->pages()->save($page);
    
            // Commit the transaction
            DB::commit();
    
            // Return success response
            return response()->json([
                'message' => 'Page created successfully.',
                'data' => $chapter
            ], 201);
    
        } catch (\Exception $err) {
            // Rollback the transaction on error
            DB::rollBack();
    
            // Return error response
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

    

    
    

    /**
     * Display the specified resource.
     */
    public function show($chapterId, $pageId)
    {
        $page = Page::where('id', $pageId)
                ->where('chapter_id', $chapterId)
                ->firstOrFail();

    return $page;
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,Chapter $chapter, Page $page)
    {
        DB::beginTransaction();
    
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'content' => 'required',
                'page_number' => 'required|integer',
            ]);
    
            // Ensure the chapter belongs to the given book
            if ($page->chapter_id !== $chapter->id) {
                return response()->json(['message' => 'Page not found in this Chapter.'], 404);
            }
    
            // Update the chapter details
            $page->update($validatedData);
    
            DB::commit();
    
            return response()->json([
                'message' => 'Page updated successfully.',
                'data' => $page
            ]);
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
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy( Chapter $chapter, Page $page)
    {
        try {
            DB::beginTransaction();
    
            // Ensure the chapter belongs to the given book
            if ($page->chapter_id !== $chapter->id) {
                return response()->json([
                    'message' => 'Page not found in this Book.',
                    'error' => true
                ], 404);
            }
    
            // Attempt to delete the chapter
            if ($page->delete()) {
                DB::commit();
                return response()->json([
                    'message' => 'Page deleted successfully.',
                    'error' => false
                ], 200);
            } else {
                DB::rollBack();
                return response()->json([
                    'message' => 'Page deletion failed. Please try again!',
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
