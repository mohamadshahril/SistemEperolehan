<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Admin role and user
        $adminRole = Role::create(['name' => 'Admin']);
        $this->admin = User::factory()->create(['email' => 'admin@example.com']);
        $this->admin->roles()->attach($adminRole);
    }

    public function test_admin_can_soft_delete_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete("/users/{$user->id}");

        $response->assertRedirect('/users');
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_admin_can_see_soft_deleted_users()
    {
        $user = User::factory()->create();
        $user->delete();

        $response = $this->actingAs($this->admin)
            ->get('/users?trashed=only');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('users.data', 1)
            ->where('users.data.0.id', $user->id)
        );
    }

    public function test_admin_can_restore_soft_deleted_user()
    {
        $user = User::factory()->create();
        $user->delete();

        $this->assertSoftDeleted('users', ['id' => $user->id]);

        $response = $this->actingAs($this->admin)
            ->post("/users/{$user->id}/restore");

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'deleted_at' => null
        ]);
    }
}
