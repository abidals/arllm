<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
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

    public function test_admin_can_update_a_category_name(): void
    {
        $category = Category::create(['name' => 'اقتصاد', 'slug' => 'economy']);

        $response = $this->actingAs($this->admin())
            ->put(route('admin.categories.update', $category), [
                'name' => 'الاقتصاد والطاقة',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'الاقتصاد والطاقة']);
    }

    public function test_admin_can_reorder_categories(): void
    {
        $first = Category::create(['name' => 'أول', 'slug' => 'one']);
        $second = Category::create(['name' => 'ثاني', 'slug' => 'two']);

        $response = $this->actingAs($this->admin())
            ->postJson(route('admin.categories.reorder'), [
                'items' => [
                    ['id' => $second->id, 'parent_id' => null, 'sort_order' => 0],
                    ['id' => $first->id, 'parent_id' => null, 'sort_order' => 1],
                ],
            ]);

        $response->assertOk();
        $this->assertSame(0, Category::find($second->id)->sort_order);
    }

    public function test_children_cannot_be_nested_deeper_than_two_levels(): void
    {
        $parent = Category::create(['name' => 'رئيسي', 'slug' => 'main']);
        $child = Category::create(['name' => 'فرعي', 'slug' => 'sub', 'parent_id' => $parent->id]);
        $victim = Category::create(['name' => 'ضحية', 'slug' => 'victim']);

        $response = $this->actingAs($this->admin())
            ->postJson(route('admin.categories.reorder'), [
                'items' => [
                    ['id' => $victim->id, 'parent_id' => $child->id, 'sort_order' => 0],
                ],
            ]);

        $response->assertStatus(422);
    }
}