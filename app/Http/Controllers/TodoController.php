<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    /**
     * List all todos
     *
     * This endpoint retrieves a list of todos, filtered by status, sorted, and searchable by title or details.
     * 
     * @queryParam status string Filter by status (completed, in progress, not started). Example: completed
     * @queryParam search string Search by title or details. Example: meeting
     * @queryParam sort string Sort by field (e.g., title:asc, status:desc). Example: title:asc
     *
     * @response 200 {
     *  "data": [
     *      {
     *          "id": 1,
     *          "title": "Sample Todo",
     *          "details": "This is a sample todo",
     *          "status": "in progress",
     *          "created_at": "2024-12-13T12:34:56Z",
     *          "updated_at": "2024-12-13T12:34:56Z"
     *      }
     *  ]
     * }
     */


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

    /**
     * Create a new todo
     *
     * This endpoint creates a new todo item.
     * 
     * @bodyParam title string required The title of the todo. Example: "Write report"
     * @bodyParam details string The details of the todo. Example: "Complete the report for Monday's meeting"
     * @bodyParam status string The status of the todo (completed, in progress, not started). Example: "not started"
     *
     * @response 201 {
     *  "id": 1,
     *  "title": "Write report",
     *  "details": "Complete the report for Monday's meeting",
     *  "status": "not started",
     *  "created_at": "2024-12-13T12:34:56Z",
     *  "updated_at": "2024-12-13T12:34:56Z"
     * }
     */
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

    /**
     * Update a todo
     *
     * This endpoint updates the details of an existing todo item.
     * 
     * @urlParam id int required The ID of the todo to update. Example: 1
     * @bodyParam title string The new title of the todo. Example: "Updated report"
     * @bodyParam details string The new details of the todo. Example: "Update the report for Tuesday's meeting"
     * @bodyParam status string The new status of the todo (completed, in progress, not started). Example: "completed"
     *
     * @response 200 {
     *  "id": 1,
     *  "title": "Updated report",
     *  "details": "Update the report for Tuesday's meeting",
     *  "status": "completed",
     *  "created_at": "2024-12-13T12:34:56Z",
     *  "updated_at": "2024-12-13T12:45:00Z"
     * }
     */

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

    /**
     * Delete a todo
     *
     * This endpoint deletes a todo by its ID.
     * 
     * @urlParam id int required The ID of the todo to delete. Example: 1
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "Todo deleted successfully"
     * }
     * @response 404 {
     *   "status": "error",
     *   "message": "Todo not found"
     * }
     */

    public function destroy($id)
    {
        try {
            $todo = Todo::findOrFail($id);

            $todo->delete();

            return response()->json(['status' => 'success', 'message' => 'Todo deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Todo not found'], 404);
        }
    }

    /**
     * Get deleted todos
     *
     * This endpoint retrieves all todos that have been soft-deleted.
     * 
     * @response 200 {
     *  "status": "success",
     *  "data": [
     *      {
     *          "id": 1,
     *          "title": "Deleted Todo",
     *          "details": "This todo has been deleted",
     *          "status": "not started",
     *          "created_at": "2024-12-01T12:00:00Z",
     *          "updated_at": "2024-12-01T12:00:00Z",
     *          "deleted_at": "2024-12-12T12:00:00Z"
     *      }
     *  ]
     * }
     *
     * 
     * @response 404 {
     *  "status": "error",
     *  "message": "Todo not found"
     * }
     */

    public function  deletedTodo()
    {
        try {
            $todo = Todo::onlyTrashed()->get();
            return response()->json(['status' => 'success', 'data' => $todo]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'An error occured', 'errorMessage' => $e->getMessage()], 500);
        }
    }

    /**
     * Restore a deleted todo
     *
     * This endpoint restores a soft-deleted todo by its ID.
     * 
     * @urlParam id int required The ID of the todo to restore. Example: 1
     *
     * @response 200 {
     *  "status": "success",
     *  "message": "Todo restored successfully!"
     * }
     *
     * @response 404 {
     *  "status": "error",
     *  "message": "Todo not found"
     * }
     */

    public function restoreTodo($id)
    {
        try {
            $todo = Todo::withTrashed()->findOrFail($id);
            $todo->restore();
            return response()->json(['status' => 'success', 'message' => 'Todo restored successfully!']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Todo not found'], 404);
        }
    }

    /**
     * Permanently delete a todo
     *
     * This endpoint permanently deletes a soft-deleted todo by its ID.
     * It ensures that the todo is already soft-deleted before attempting permanent deletion.
     * 
     * @urlParam id int required The ID of the todo to delete permanently. Example: 1
     *
     * @response 200 {
     *  "status": "success",
     *  "message": "Todo permanently deleted"
     * }
     *
     * @response 400 {
     *  "status": "error",
     *  "message": "Todo is not deleted"
     * }
     *
     * @response 404 {
     *  "status": "error",
     *  "message": "Todo not found"
     * }
     */
    public function deletePermanently($id)
    {
        try {

            $todo = Todo::withTrashed()->findOrFail($id);

            if (!$todo->trashed()) {
                return response()->json(['status' => 'error', 'message' => 'Todo is not deleted'], 400);
            }

            $todo->forceDelete();

            return response()->json(['status' => 'success', 'message' => 'Todo permanently deleted']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Todo not found'], 404);
        }
    }
}
