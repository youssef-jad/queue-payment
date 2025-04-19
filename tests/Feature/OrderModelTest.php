<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;

use Illuminate\Support\Facades\DB;


class OrderModelTest extends TestCase
{
    use RefreshDatabase;


    public function testCreateOrders(): void
    {
        $user = User::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'amount' => 200.12,
            'status' => 'pending'
        ]);
        
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'user_id' => $user->id,
            'amount' => 200.12,
            'status' => 'pending'
        ]);
    } 
}
