<?php

namespace Tests\Feature;

use App\Models\Todo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TodoFeatureTest extends TestCase
{

    public function test_home_page_loads_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_can_create_todo()
    {
        $response = $this->postJson('/api/todos', [
            'title' => 'Test Todo',
            'details' => 'This is a test todo',
            'status' => 'not started'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('todos', [
            'title' => 'Test Todo',
            'details' => 'This is a test todo',
            'status' => 'not started'
        ]);
    }

    /**
     * Test filtering todos by status.
     */
    public function test_can_filter_todos_by_status()
    {
        Todo::factory()->create(['status' => 'not started']);
        Todo::factory()->create(['status' => 'completed']);

        $response = $this->getJson('/api/todos?status=completed');

        $response->assertStatus(200);
        // $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['status' => 'completed']);
    }

    /**
     * Test searching todos by title or details.
     */
    public function test_can_search_todos_by_title_or_details()
    {
        Todo::factory()->create(['title' => 'Meeting', 'details' => 'Discuss project progress']);
        Todo::factory()->create(['title' => 'Grocery', 'details' => 'Buy milk and bread']);

        $response = $this->getJson('/api/todos?search=Meeting');

        $response->assertStatus(200);
        $response->assertJsonFragment(['title' => 'Meeting']);
    }

    /**
     * Test updating a todo.
     */
    public function test_can_update_todo()
    {
        $todo = Todo::factory()->create();

        $response = $this->putJson("/api/todos/{$todo->id}", [
            'title' => 'Updated Todo',
            'details' => 'Updated details',
            'status' => 'completed'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'title' => 'Updated Todo',
            'details' => 'Updated details',
            'status' => 'completed'
        ]);
    }

    /**
     * Test deleting a todo.
     */
    public function test_can_delete_todo()
    {
        $todo = Todo::factory()->create();

        $response = $this->deleteJson("/api/todos/{$todo->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('todos', ['id' => $todo->id]);
    }

    /**
     * Test retrieving deleted todos.
     */
    public function test_can_get_deleted_todos()
    {
        $todo = Todo::factory()->create();
        $todo->delete();

        $response = $this->getJson('/api/todos/deleted');

        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $todo->id]);
    }

    /**
     * Test restoring a deleted todo.
     */
    public function test_can_restore_deleted_todo()
    {
        $todo = Todo::factory()->create();
        $todo->delete();

        $response = $this->getJson("/api/todos/restore/{$todo->id}");

        $response->assertStatus(200);

        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'details' => $todo->details,
            'status' => $todo->status,
            'deleted_at' => null
        ]);
    }

    /**
     * Test permanently deleting a todo.
     */
    public function test_can_permanently_delete_todo()
    {
        $todo = Todo::factory()->create();
        $todo->delete();

        $response = $this->deleteJson("/api/todos/delete-perm/{$todo->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('todos', ['id' => $todo->id]);
    }


    /**
     * Test listing all todos.
     */
    public function test_can_list_todos()
    {
        Todo::factory()->count(3)->create();

        $response = $this->getJson('/api/todos');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                '*' => ['id', 'title', 'details', 'status', 'created_at', 'updated_at']
            ]
        ]);
    }
}
