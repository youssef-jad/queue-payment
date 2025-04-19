<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;

use Illuminate\Support\Facades\DB;


class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function testSedderOfUsers(): void
    {
        $seeder = new \Database\Seeders\Users();
        $seeder->run();
        $this->assertEquals(100, User::count());
        $this->assertDatabaseCount('users', 100);
    } 

    public function testSeederOfOrders() : void
    {
        $seeder = new \Database\Seeders\Orders();
        $seeder->run();
        $this->assertEquals(100, Order::count());
        $this->assertDatabaseCount('orders', 100);
    }
}
