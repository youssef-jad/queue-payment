<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Order;
class Orders extends Seeder
{

    public function run(): void
    {
        $users = User::count() === 0 ? User::factory(10)->create() : User::limit(10)->get();
        Order::factory(100)
            ->recycle($users)
            ->create();
    }
}
