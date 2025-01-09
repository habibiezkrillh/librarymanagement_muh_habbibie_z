<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\Librarian;
use App\Models\Collection;
use App\Models\User;

class LibraryManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_create_librarian()
    {
        $admin = Admin::factory()->create();

        $response = $this->post('/librarians', [
            'name' => 'Test Librarian',
            'email' => 'librarian@example.com',
            'admin_id' => $admin->id,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('librarians', ['email' => 'librarian@example.com']);
    }

    /** @test */
    public function librarian_can_add_collection()
    {
        $librarian = Librarian::factory()->create();

        $response = $this->post('/collections', [
            'title' => 'Harry Potter',
            'type' => 'book',
            'is_physical' => true,
            'librarian_id' => $librarian->id,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('collections', ['title' => 'Harry Potter']);
    }

    /** @test */
    public function librarian_cannot_create_collection_without_valid_data()
    {
        $response = $this->post('/collections', []);

        $response->assertStatus(422); // Validation Error
    }

    /** @test */
    public function librarian_can_update_collection()
    {
        $librarian = Librarian::factory()->create();
        $collection = Collection::factory()->create(['librarian_id' => $librarian->id]);

        $response = $this->put("/collections/{$collection->id}", [
            'title' => 'Updated Book Vol 10',
            'type' => 'journal',
            'is_physical' => false,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('collections', ['title' => 'Updated Book Vol 10']);
    }

    /** @test */
    public function librarian_can_delete_collection()
    {
        $librarian = Librarian::factory()->create();
        $collection = Collection::factory()->create(['librarian_id' => $librarian->id]);

        $response = $this->delete("/collections/{$collection->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('collections', ['id' => $collection->id]);
    }

    /** @test */
    public function librarian_can_approve_access_request()
    {
        $librarian = Librarian::factory()->create();
        $user = User::factory()->create();
        $collection = Collection::factory()->create();

        $response = $this->post('/access-requests', [
            'collection_id' => $collection->id,
            'user_id' => $user->id,
            'librarian_id' => $librarian->id,
            'status' => 'pending',
        ]);

        $response->assertStatus(201);
        $accessRequest = $response->json();

        $updateResponse = $this->put("/access-requests/{$accessRequest['id']}", [
            'status' => 'approved',
        ]);

        $updateResponse->assertStatus(200);
        $this->assertDatabaseHas('access_requests', [
            'id' => $accessRequest['id'],
            'status' => 'approved',
        ]);
    }

    /** @test */
    public function librarian_can_reject_access_request()
    {
        $librarian = Librarian::factory()->create();
        $user = User::factory()->create();
        $collection = Collection::factory()->create();

        $response = $this->post('/access-requests', [
            'collection_id' => $collection->id,
            'user_id' => $user->id,
            'librarian_id' => $librarian->id,
            'status' => 'pending',
        ]);

        $response->assertStatus(201);
        $accessRequest = $response->json();

        $updateResponse = $this->put("/access-requests/{$accessRequest['id']}", [
            'status' => 'rejected',
        ]);

        $updateResponse->assertStatus(200);
        $this->assertDatabaseHas('access_requests', [
            'id' => $accessRequest['id'],
            'status' => 'rejected',
        ]);
    }
}