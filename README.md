# Todo App API

A simple Laravel CRUD API for managing todos with features like filtering, searching, and sorting.

# Features

List Todos:

Filter by status (completed, in progress, not started).
Search by title and details.
Sortable and filterable.
Create Todo: Add new todos with title, details, and status.

Update Todo: Modify existing todos.

Delete Todo: Remove todos by ID.

Automated Tests: Includes unit and end-to-end tests with high code coverage.

Generated API Documentation: Comprehensive documentation for all endpoints, including parameters and responses.

# Endpoints

List Todos
GET /todos

Query parameters:
status (filter by status)
search (search by title or details)
sort (e.g., created_at, title)
Create Todo
POST /todos

# Request body:

title: string
details: string
status: string (completed, in progress, not started)

# Update Todo

PUT /todos/{id}
Request body: Same as create.

# Delete Todo

DELETE /todos/{id}
