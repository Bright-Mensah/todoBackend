<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TodoFeatureTest extends TestCase
{
    /**
     * A basic feature test example.
     */
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
}
