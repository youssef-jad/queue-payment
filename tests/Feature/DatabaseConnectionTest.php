<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;

use Illuminate\Support\Facades\DB;


class DatabaseConnectionTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function testDBConnectionToTesting(): void
    {
        $this->assertTrue(\DB::connection()->getDatabaseName() === 'testing');
        try {
            $result = DB::select('SELECT 1');
            $this->assertTrue(true); // If we get here, connection works
        } catch (\Exception $e) {
            $this->fail('Database connection failed: ' . $e->getMessage());
        }
    }
}
