<?php

use App\Models\Table;
use App\Models\User;
use Illuminate\Support\Str;

class TableTest extends TestCase
{
    /**
     * @test
     */
    public function can_create_a_table(): void
    {
        $table = Table::factory()->make();
        $name = $table->name;

        $user = User::factory()->create([
            'role' => 'admin'
        ]);

        $this->actingAs($user)
            ->json('POST', '/api/tables', [
            'name' => $name
        ]);
        $slug = Str::slug($name);

        // Then
        // The return response code is 'Created' (201)
        $this->assertResponseStatus(201);

        // Confirm the data returned is the same
        $this->seeJsonContains([
            'name'  => $table->name,
            'slug'  => $table->slug
        ]);

        // And the database has the record
        $this->seeInDatabase('tables', [
            'name'  => $name,
            'slug'  => $slug,
        ]);
    }

    /**
     * @test
     */
    public function can_return_a_table(): void
    {
        // Given
        $table = Table::factory()->create();

        // When
        $user = User::factory()->create();

        $this->actingAs($user)
            ->json('GET', '/api/tables/' . $table->id);

        // Then
        $this->assertResponseOk();

        // Then
        $this->seeInDatabase('tables', [
            'name'  => $table->name,
            'slug'  => $table->slug
        ]);

        // Then
        $this->seeJsonContains([
            'name'  => $table->name,
            'slug'  => $table->slug
        ]);
    }

    /**
     * @test
     */
    public function will_fail_with_a_404_if_table_is_not_found(): void
    {
        // Given
        // Table 999 does not exist.
        // When
        $user = User::factory()->create();

        $this->actingAs($user)->json('GET', '/api/tables/999');
        // Then
        $this->assertResponseStatus(404);
    }

    /**
     * @test
     */
    public function will_fail_with_a_404_if_table_we_want_to_update_is_not_found(): void
    {
        // Given no table
        // When
        $user = User::factory()->create([
            'role' => 'admin'
        ]);

        $this->actingAs($user)->json('PUT', '/api/tables/999', [
            'name' => 'OK updated'
        ]);

        // Then
        $this->assertResponseStatus(500);

        // Then
        $this->seeJson([
            'error' => 'update_error'
        ]);
    }

    /**
     * @test
     */
    public function can_update_a_table(): void
    {
        // Given
        $table = Table::factory()->create();

        // When
        $newTable = [
            'name'  => $table->name . '_updated',
            'slug'  => Str::slug($table->name . '_updated'),
        ];

        $user = User::factory()->create([
            'role' => 'admin'
        ]);

        $this->actingAs($user)->json('PUT', '/api/tables/'. $table->id, $newTable);

        // Then
        $this->assertResponseOk();

        // Then
        $this->seeJsonContains($newTable);

        // Then
        $this->seeInDatabase(
            'tables',
            [
                'id' => $table->id,
                'name' => $newTable['name'],
                'slug' => $newTable['slug'],
            ]
        );
    }

    /**
     * @test
     */
    public function will_fail_with_a_404_if_table_we_want_to_delete_is_not_found(): void
    {
        // Given
        // When
        $user = User::factory()->create([
            'role' => 'admin'
        ]);

        $this->actingAs($user)->json('DELETE', '/api/tables/999');
        // Then
        $this->assertResponseStatus(404);
        $this->seeJson([
            'error' => 'delete_error'
        ]);
    }

    /**
     * @test
     */
    public function can_delete_a_table(): void
    {
        // Given
        $table = Table::factory()->create();
        // When
        $user = User::factory()->create([
            'role' => 'admin'
        ]);

        $this->actingAs($user)->json('DELETE', '/api/tables/' . $table->id);
        // Then
        $this->assertResponseOk();

        $this->notSeeInDatabase('tables', [
            'id' => $table->id,
        ]);
    }

    /**
     * @test
     */
    public function can_return_a_collection_of_paginated_tables(): void
    {
        $tables = Table::factory()->count(3)->create();

        $user = User::factory()->create();

        $this->actingAs($user)->json('GET', '/api/tables');

        $this->assertResponseOk();

        // Then, the database contains 3 records
        self::assertSame(3, Table::all()->count());

        // Then
        $this->seeJsonStructure([
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
}
