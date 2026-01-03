<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthGuardTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_is_redirected_to_admin_index_and_session_guard_is_set()
    {
        // ensure role exists and attach by id
        $role = \App\Models\Role::create(['name' => 'admin']);
        $admin = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($admin)->get('/contacts');

        $response->assertRedirect(route('admin.index'));

        // after the redirect, the session guard should be set
        $this->assertEquals('admin', session('guard'));
    }

    /** @test */
    public function user_is_redirected_to_contacts_and_cannot_access_admin()
    {
        $role = \App\Models\Role::create(['name' => 'user']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($user)->get('/contacts');

        $response->assertRedirect(route('contacts.index'));

        $this->assertEquals('user', session('guard'));

        // user should be forbidden from admin route
        $this->actingAs($user);
        $resp = $this->get('/admin');
        $resp->assertStatus(403);
    }

    /** @test */
    public function logout_clears_guard_and_redirects_to_login_with_status()
    {
        $adminRole = \App\Models\Role::create(['name' => 'admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        // ensure guard is set first by visiting an authenticated route
        $this->actingAs($admin)->get('/contacts');

        $response = $this->post(route('logout'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', 'Admin logged out successfully.');

        $this->assertGuest();
        $this->assertNull(session('guard'));
    }
}
