<?php

use App\Models\User;

class IncomeReportTest extends TestCase
{
    public function test_can_view_json_income_report()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->json('GET', '/api/reports/income-report?start_date=2021-01-01&end_date='.date('Y-m-d'));

        $this->assertResponseOk();
    }
}
