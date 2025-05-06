<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Response;
use App\Models\Book;
use Exception;

class ChapterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Book $book)
    {
        $dataList = $book->chapters()->orderBy('id', 'desc')->get();
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
                'book_id' => 'required|exists:books,id',
                'title' => 'required|string|max:255',
                'chapter_number' => 'required|integer',
            ]);

            // Find the bookshelf the book will belong to
            $book = Book::findOrFail($validatedData['book_id']);

            // Create the new book and associate it with the bookshelf
            $chapter = new Chapter([
                'title' => $validatedData['title'],
                'chapter_number' => $validatedData['chapter_number'],

            ]);

            // Save the book and associate it with the bookshelf
            $book->chapters()->save($chapter);

            // Commit the transaction
            DB::commit();

            // Return success response
            return response()->json([
                'message' => 'Chapter created successfully.',
                'data' => $book
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
    public function show($bookId, $chapterId)
    {
        $chapter = Chapter::where('id', $chapterId)
            ->where('book_id', $bookId)
            ->firstOrFail();

        return $chapter;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book, Chapter $chapter)
    {
        DB::beginTransaction();

        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'chapter_number' => 'required|integer',
            ]);

            // Ensure the chapter belongs to the given book
            if ($chapter->book_id !== $book->id) {
                return response()->json(['message' => 'Chapter not found in this Book.'], 404);
            }

            // Update the chapter details
            $chapter->update($validatedData);

            DB::commit();

            return response()->json([
                'message' => 'Chapter updated successfully.',
                'data' => $chapter
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
    public function destroy(Book $book, Chapter $chapter)
    {
        try {
            DB::beginTransaction();

            // Ensure the chapter belongs to the given book
            if ($chapter->book_id !== $book->id) {
                return response()->json([
                    'message' => 'Chapter not found in this Book.',
                    'error' => true
                ], 404);
            }

            // Attempt to delete the chapter
            if ($chapter->delete()) {
                DB::commit();
                return response()->json([
                    'message' => 'Chapter deleted successfully.',
                    'error' => false
                ], 200);
            } else {
                DB::rollBack();
                return response()->json([
                    'message' => 'Chapter deletion failed. Please try again!',
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


    public function fullContent(Chapter $chapter)
    {
        $content = $chapter->pages()->orderBy('page_number')->pluck('content')->implode("\n\n");
        return response()->json([
            'chapter_title' => $chapter->title,
            'full_content' => $content,
        ]);
    }
}
