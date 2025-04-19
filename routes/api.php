<?php

use Illuminate\Support\Facades\Route;

Route::post('/create-order', function () {
    $order = App\Models\Order::create([
        'user_id' => request()->user_id,
        'amount' => request()->amount,
        'status' => 'pending'
    ]);
    
    \App\Jobs\ProcessPayment::dispatch($order->id);
    return response()->json($order, 201);
});
