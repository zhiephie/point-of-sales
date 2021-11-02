<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Str;

class CategoryTest extends TestCase
{

    /**
     * @test
     */
    public function can_return_a_collection_of_paginated_categories(): void
    {
        Category::factory()->count(5)->create();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->json('GET', '/api/categories')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'data' => [
                    'current_page',
                    'data' => [],
                    'first_page_url',
                    'from',
                    'last_page',
                    'last_page_url',
                    'links' => [],
                    'next_page_url',
                    'path',
                    'per_page',
                    'prev_page_url',
                    'to',
                    'total'
                ],
                'error'
            ]);
    }

    /**
     * @test
     */
    public function can_create_a_category(): void
    {
        $category = Category::factory()->make();

        $user = User::factory()->create([
            'role' => 'admin'
        ]);

        $this->actingAs($user)->json('POST', '/api/categories', [
            'name' => $category->name
        ]);

        $this->assertResponseStatus(201);

        $this->seeJsonContains([
            'name'  => $category->name,
            'slug'  => $category->slug
        ]);

        $this->seeInDatabase('categories', [
            'name'  => $category->name,
            'slug'  => $category->slug,
        ]);
    }

    /**
     * @test
     */
    public function will_fail_with_a_404_if_category_is_not_found(): void
    {
        // Given
        // Table 999 does not exist.
        // When
        $user = User::factory()->create();

        $this->actingAs($user)->json('GET', '/api/categories/999');
        // Then
        $this->assertResponseStatus(404);
    }

    /**
     * @test
     */
    public function can_return_a_category(): void
    {
        $category = Category::factory()->create();

        $user = User::factory()->create();

        $this->actingAs($user)->json('GET', '/api/categories/' . $category->id);

        $this->assertResponseOk();

        $this->seeInDatabase('categories', [
            'name'  => $category->name,
            'slug'  => $category->slug
        ]);

        $this->seeJsonContains([
            'name'  => $category->name,
            'slug'  => $category->slug
        ]);
    }

    /**
     * @test
     */
    public function can_update_a_category()
    {
        $category = Category::factory()->create();

        $newCategory = [
            'name'  => $category->name . '_updated',
            'slug'  => Str::slug($category->name . '-updated'),
        ];

        $user = User::factory()->create([
            'role' => 'admin'
        ]);

        $this->actingAs($user)->json('PUT', '/api/categories/' .$category->id, $newCategory);

        $this->assertResponseOk();

        $this->seeJsonContains($newCategory);

        $this->seeInDatabase(
            'categories',
            [
                'id' => $category->id,
                'name' => $newCategory['name'],
                'slug' => $newCategory['slug'],
            ]
        );
    }

    /**
     * @test
     */
    public function will_fail_with_a_404_if_category_we_want_to_update_is_not_found(): void
    {
        // Given no category
        // When
        $user = User::factory()->create([
            'role' => 'admin'
        ]);

        $this->actingAs($user)->json('PUT', '/api/categories/999', [
            'name' => 'OK updated'
        ]);

        $this->assertResponseStatus(500);

        $this->seeJson([
            'error' => 'update_error'
        ]);
    }

    /**
     * @test
     */
    public function will_fail_with_a_404_if_category_we_want_to_delete_is_not_found(): void
    {
        // Given
        // When
        $user = User::factory()->create([
            'role' => 'admin'
        ]);

        $this->actingAs($user)->json('DELETE', '/api/categories/999');
        // Then
        $this->assertResponseStatus(404);
        $this->seeJson([
            'error' => 'delete_error'
        ]);
    }

    /**
     * @test
     */
    public function can_delete_a_category(): void
    {
        // Given
        $category = Category::factory()->create();
        // When
        $user = User::factory()->create([
            'role' => 'admin'
        ]);

        $this->actingAs($user)->json('DELETE', '/api/categories/' . $category->id);
        // Then
        $this->assertResponseOk();

        $this->notSeeInDatabase('categories', [
            'id' => $category->id,
        ]);
    }
}
