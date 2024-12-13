<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Exception;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    public function index(Request $request)
    {



        $query = Todo::query();

        // Filtering by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Searching by title or details
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('details', 'like', '%' . $request->search . '%');
            });
        }


        $request->validate([
            'sort_by' => 'in:title,status,created_at',
            'sort_direction' => 'in:asc,desc',
        ]);



        // Sorting

        $sortBy = $request->get('sort_by', 'created_at'); // Default column to sort
        $sortDirection = $request->get('sort_direction', 'asc'); // Default direction
        $query->orderBy($sortBy, $sortDirection);

        return response()->json(["status" => "success", "data" => $query->get()]);
    }

    public function store(Request $request)
    {

        try {


            $todo = Todo::create($request->validate([
                'title' => 'required|string|max:255',
                'details' => 'required|string',
                'status' => 'required|in:completed,in progress,not started',
            ]));

            return response()->json(["status" => "success", "message" => "Todo created successfully", "todo" => $todo], 201);
        } catch (\Exception $ex) {
            return response()->json(["error" => $ex->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string',
            'details' => 'sometimes|required|string',
            'status' => 'sometimes|required|in:completed,in progress,not started',
        ]);

        $todo = Todo::findOrFail($id);
        $todo->update($validated);

        return response()->json(["status" => "success", "message" => "Todo updated successfully", "data" => $todo]);
    }

    public function destroy($id)
    {
        $todo = Todo::findOrFail($id);

        if (!$todo) {
            return response()->json(['status' => 'error', 'message' => 'Todo not found'], 404);
        }
        $todo->delete();

        return response()->json(['status' => 'success', 'message' => 'Todo deleted successfully']);
    }
}
