<?php

namespace Tests\Feature;

use App\Constants\Roles;
use App\Models\User;
use Database\Seeders\RolesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PhpParser\Node\Expr\Assign;
use Tests\TestCase;

class UserModuleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_loads_the_laravel_welcome_page()
    {
        $this->get('/')
        ->assertStatus(200)
        ->assertSee('Log in');
    }

    /** @test */
    public function it_loads_the_login_user_page()
    {
        $this->get('/login')
        ->assertStatus(200)
        ->assertSee('Login');
    }

    /** @test */
    public function the_login_page_displays_the_login_form()
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /** @test */
    public function the_login_page_displays_validation_errors()
    {
        $response = $this->post(route('login'), []);
        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function authorized_user_can_access_to_users_module() 
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('users.index'));

        $response->assertForbidden();
    }


    /** @test */
    public function it_displays_a_users_list() 
    {
        $admin = $this->CreateAdminUser();

        $users = User::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('users.index'));

        $response->assertOk()
            ->assertViewIs('users.index')
            ->assertViewHas('data')
            ->assertSeeText($users->first()->email);
    }

    /** @test */
    public function it_displays_new_user_form() 
    {
        $admin = $this->CreateAdminUser();
        
        $response = $this->actingAs($admin)->get(route('users.create'));

        $response->assertOk()
            ->assertViewIs('users.create')
            ->assertViewHas('roles');
    }

    /** @test */
    public function it_saves_a_new_user() 
    {
        /** @var \App\Models\User $admin */
        $admin = $this->CreateAdminUser();
        $user = User::factory()->make();
        $data = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 12345678,
            'confirm-password' => 12345678,
            'roles' => Roles::USER,
        ];

        $response = $this->actingAs($admin)
            ->post(route('users.store',$data));

        $response->assertRedirect(route('users.index'))
            ->assertSessionHasNoErrors();
            
        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseHas('users', [
            'name' => $data['name'],
            'email' => $data['email'],
        ]);
    }

    /** @test */
    public function it_can_not_saves_a_new_user() 
    {
        /** @var \App\Models\User $admin */
        $user = User::factory()->make();
        $data = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 12345678,
            'confirm-password' => 12345678,
            'roles' => Roles::USER,
        ];

        $response = $this->actingAs($user)
            ->post(route('users.store',$data));

        $response->assertForbidden();
    }

    /** @test */
    public function it_updates_a_previously_created_user() 
    {
        /** @var \App\Models\User $admin */
        $admin = $this->CreateAdminUser();
        $user = User::factory()->make();
        $newData = [
            'name' => $user->name,
        ];

        $response = $this->actingAs($admin)
            ->put(route('users.store',$newData));

        $response->assertSee('User updated successfully');

    }

    // /** @test */
    // public function it_deletes_a_previously_created_user() 
    // {
    //     /** @var \App\Models\User $admin */
    //     $admin = $this->CreateAdminUser();
    //     $user = User::factory()->create();

    //     $response = $this->actingAs($admin)
    //         ->put(route('users.destroy',$user['id']));

    //     $response->assertSee("User deleted successfully");
    // }

        
    private function CreateAdminUser(): User
    {
        $this->seed(RolesTableSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole(Roles::ADMIN);

        return $admin;
    }

}


