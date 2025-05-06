<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Response;
use App\Models\Bookshelf;
use Exception;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Bookshelf $bookshelf)
    {
        $dataList = $bookshelf->books()->orderBy('id', 'desc')->get();
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
                'bookshelf_id' => 'required|exists:bookshelves,id',
                'title' => 'required|string|max:255',
                'author' => 'required|string|max:255',
                'published_year' => 'required|integer',
            ]);

            // Find the bookshelf the book will belong to
            $bookshelf = Bookshelf::findOrFail($validatedData['bookshelf_id']);

            // Create the new book and associate it with the bookshelf
            $book = new Book([
                'title' => $validatedData['title'],
                'author' => $validatedData['author'],
                'published_year' => $validatedData['published_year'],
            ]);

            // Save the book and associate it with the bookshelf
            $bookshelf->books()->save($book);

            // Commit the transaction
            DB::commit();

            // Return success response
            return response()->json([
                'message' => 'Book created successfully.',
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
    public function show($bookshelfId, $bookId)
    {
        $book = Book::where('id', $bookId)
            ->where('bookshelf_id', $bookshelfId)
            ->firstOrFail();

        return $book;
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bookshelf $bookshelf, Book $book)
    {
        // Ensure the book belongs to the bookshelf
        if ($book->bookshelf_id !== $bookshelf->id) {
            return response()->json(['message' => 'Book not found in this bookshelf.'], 404);
        }

        DB::beginTransaction();

        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'author' => 'required|string|max:255',
                'published_year' => 'required|integer',
            ]);

            // Update the book's details
            $book->update($validatedData);

            DB::commit();

            return response()->json([
                'message' => 'Book updated successfully.',
                'data' => $book
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
    public function destroy(Bookshelf $bookshelf, Book $book)
    {
        try {
            DB::beginTransaction();

            // Ensure the book belongs to the bookshelf
            if ($book->bookshelf_id !== $bookshelf->id) {
                return response()->json([
                    'message' => 'Book not found in this bookshelf.',
                    'error' => true
                ], 404);
            }

            // Attempt to delete the book
            if ($book->delete()) {
                DB::commit();
                return response()->json([
                    'message' => 'Book deleted successfully.',
                    'error' => false
                ], 200);
            } else {
                DB::rollBack();
                return response()->json([
                    'message' => 'Book deletion failed. Please try again!',
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



    public function search(Request $request)
    {
        try {
            $query = Book::query();
    
            if ($request->filled('title')) {
                $query->where('title', 'like', '%' . $request->title . '%');
            }
    
            if ($request->filled('author')) {
                $query->where('author', 'like', '%' . $request->author . '%');
            }
    
            return response()->json($query->get());
    
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
    
}
