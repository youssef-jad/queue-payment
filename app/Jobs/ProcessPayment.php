<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 5;

    protected readonly int $orderId;

    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }

    public function handle(): void
    {
        $order = Order::findOrFail($this->orderId);

        if ($order->status === 'completed') {
            Log::info("Order already completed", ['order_id' => $this->orderId]);
            return;
        }

        $order->update(['status' => 'processing']);
        Log::info("Order is processing", ['order_id' => $this->orderId]);

        $paymentSucceeded = $this->simulateGateway();

        if ($paymentSucceeded) {
            $order->update(['status' => 'completed']);
            Log::info("Order completed successfully", ['order_id' => $this->orderId]);
        } else {
            $order->update(['status' => 'failed']);
            Log::error("Payment failed", ['order_id' => $this->orderId]);
            throw new \Exception("Payment processing failed for order {$this->orderId}");
        }
    }

    public function failed(\Throwable $exception): void
    {
        $order = Order::find($this->orderId);
        if ($order && $order->status !== 'completed') {
            $order->update(['status' => 'failed']);
        }

        Log::error("Job permanently failed", [
            'order_id' => $this->orderId,
            'error' => $exception->getMessage()
        ]);
    }

    protected function simulateGateway(): bool
    {
        sleep(2);
        return rand(1, 10) <= 7;
    }
}
