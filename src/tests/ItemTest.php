<?php

use App\Models\Item;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Str;

class ItemTest extends TestCase
{
    /**
    * @test
    */
    public function can_return_a_collection_of_paginated_items(): void
    {
        Item::factory()->count(5)->create();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->json('GET', '/api/items')
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
    public function can_create_a_item(): void
    {
        $category = Category::factory()->create();
        $item = Item::factory()->make([
            'category_id' => $category->id
        ]);

        $user = User::factory()->create([
            'role' => 'admin'
        ]);

        $this->actingAs($user)
            ->json('POST', '/api/items', [
                'category_id' => $item->category_id,
                'name' => $item->name,
                'barcode' => $item->barcode,
                'description' => $item->description,
                'price' => $item->price,
                'quantity' => $item->quantity
            ]);

        $this->assertResponseStatus(201);

        $this->seeJsonContains([
            'category_id' => $category->id,
            'barcode' => $item->barcode,
            'name' => $item->name,
            'description' => $item->description,
            'price' => $item->price,
            'quantity' => $item->quantity
        ]);

        $this->seeInDatabase('items', [
            'category_id' => $category->id,
            'barcode' => $item->barcode,
            'name' => $item->name,
            'description' => $item->description,
            'price' => $item->price,
            'quantity' => $item->quantity
        ]);
    }

    /**
     * @test
     */
    public function can_return_a_item(): void
    {
        $item = Item::factory()->create();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->json('GET', '/api/items/' . $item->id);

        $this->assertResponseOk();

        $this->seeInDatabase('items', [
            'id' => $item->id,
            'name' => $item->name,
        ]);

        $this->seeJsonContains([
            'id' => $item->id,
            'category_id' => (string) $item->category_id,
            'barcode' => $item->barcode,
            'name' => $item->name,
            'description' => $item->description,
            'price' => (string) $item->price,
            'quantity' => (string) $item->quantity
        ]);
    }

    /**
     * @test
     */
    public function will_fail_with_a_404_if_item_is_not_found(): void
    {
        // Given
        // Item 999 does not exist.
        // When
        $user = User::factory()->create();

        $this->actingAs($user)->json('GET', '/api/items/999');
        // Then
        $this->assertResponseStatus(404);
    }

    /**
     * @test
     */
    public function can_update_a_item(): void
    {
        $category = Category::factory()->create();
        $item = Item::factory()->create([
            'category_id' => $category->id
        ]);

        $newItem = [
            'category_id' => $item->category_id,
            'barcode' => $item->barcode,
            'name'  => $item->name . '_updated',
            'description' =>  $item->description . '_updated',
            'price' => $item->price,
            'quantity' => $item->quantity,
            'slug'  => Str::slug($item->name . '-updated'),
        ];

        $user = User::factory()->create([
            'role' => 'admin'
        ]);

        $this->actingAs($user)->json('PUT', '/api/items/' . $item->id, $newItem);

        $this->assertResponseOk();

        $this->seeJsonContains($newItem);
    }

    /**
     * @test
     */
    public function will_fail_with_a_404_if_item_we_want_to_update_is_not_found(): void
    {
        $item = Item::factory()->create();
        $category = Category::factory()->create();
        // Given no item
        // When
        $user = User::factory()->create([
            'role' => 'admin'
        ]);

        $this->actingAs($user)->json('PUT', '/api/items/999', [
            'category_id' => $category->id,
            'barcode' => $item->barcode,
            'name'  => $item->name . '_updated',
            'description' =>  $item->description . '_updated',
            'price' => $item->price,
            'quantity' => $item->quantity
        ]);

        $this->assertResponseStatus(500);

        $this->seeJson([
            'error' => 'update_error'
        ]);
    }

    /**
     * @test
     */
    public function can_delete_a_item(): void
    {
        $item = Item::factory()->create();

        $user = User::factory()->create([
            'role' => 'admin'
        ]);

        $this->actingAs($user)->json('DELETE', '/api/items/' . $item->id);

        $this->assertResponseOk();

        $this->notSeeInDatabase('items', [
            'id' => $item->id,
        ]);
    }

    /**
     * @test
     */
    public function will_fail_with_a_404_if_item_we_want_to_delete_is_not_found(): void
    {
        // Given
        // When
        $user = User::factory()->create([
            'role' => 'admin'
        ]);

        $this->actingAs($user)->json('DELETE', '/api/items/999');
        // Then
        $this->assertResponseStatus(404);
        $this->seeJson([
            'error' => 'delete_error'
        ]);
    }
}
