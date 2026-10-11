<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsPageTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $this->seed(RolesSeeder::class);
        $user = User::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => 'Pass@12345',
        ]);
        $user->assignRole('admin');

        return $user->refresh();
    }

    public function test_settings_page_renders_for_admin_with_remove_buttons(): void
    {
        Category::create(['name' => 'اختبار', 'slug' => 'test-cat']);

        $response = $this->actingAs($this->adminUser())->get(route('admin.settings.edit'));

        $response->assertOk();
        $response->assertSee('عنوان التدفق الرئيسي');
        $response->assertSee('موقع قائمة الأقسام');
    }

    public function test_profile_page_renders_with_avatar_remove_button(): void
    {
        $user = $this->adminUser();

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertOk();
        $response->assertSee('صورة شخصية جديدة');
    }

    public function test_admin_cannot_delete_last_admin(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->delete(route('admin.users.destroy', $admin));

        $response->assertSessionHasErrors();
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}