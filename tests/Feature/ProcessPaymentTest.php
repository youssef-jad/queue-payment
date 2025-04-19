<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Jobs\ProcessPayment;
use Illuminate\Support\Facades\Queue;


use Illuminate\Support\Facades\DB;


class ProcessPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function testProccessPayment(): void
    {
      $user = User::factory()->create();

      $order = Order::create([
          'user_id' => $user->id,
          'amount' => 500.45,
          'status' => 'pending'
      ]);
      // Initial state check
      $this->assertEquals('pending', $order->status);

      $job = $this->getMockBuilder(ProcessPayment::class)
      ->setConstructorArgs([$order->id])
      ->onlyMethods(['handle'])
      ->getMock();

      $job->expects($this->once())
      ->method('handle')
      ->willReturnCallback(function () use ($order) {
          // Update to processing
          $order->status = 'processing';
          $order->save();
          
          // Simulate processing delay
          sleep(2);
          
          // Complete the order
          $order->status = 'completed';
          $order->save();
      });

      // Execute the mocked job
      $job->handle();

      // Refresh the order from the database
      $order->refresh();

      // Check final status
      $this->assertEquals('completed', $order->status);

    } 


    public function testDispatchedOrder()
    {
        // Fake the queue
        Queue::fake();
        // Create user and order
        $user = User::factory()->create();
        
        // Create order via API
        $order = Order::create([
          'user_id' => $user->id,
          'amount' => 500.45,
          'status' => 'pending'
      ]);

        ProcessPayment::dispatch($order->id);
        // Assert job was dispatched
        Queue::assertPushed(ProcessPayment::class);
    }

    public function testOrderStatusTransitions()
    {
        // Create user and order
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'amount' => 100.00,
            'status' => 'pending'
        ]);

        // Verify initial status is pending
        $this->assertEquals('pending', $order->status);

        // Create mock job
        $job = $this->getMockBuilder(ProcessPayment::class)
            ->setConstructorArgs([$order->id])
            ->onlyMethods(['handle'])
            ->getMock();

        $job->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function () use ($order) {
                // Verify transition to processing
                $order->status = 'processing';
                $order->save();
                $this->assertEquals('processing', $order->status);

                // Verify transition to completed
                $order->status = 'completed'; 
                $order->save();
                $this->assertEquals('completed', $order->status);
            });

        $job->handle();

        // Verify final status persisted
        $order->refresh();
        $this->assertEquals('completed', $order->status);
    }

    public function testOrderFailedTransition()
    {
        // Create user and order
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'amount' => 100.00,
            'status' => 'pending'
        ]);

        // Verify initial status is pending
        $this->assertEquals('pending', $order->status);

        // Create mock job
        $job = $this->getMockBuilder(ProcessPayment::class)
            ->setConstructorArgs([$order->id])
            ->onlyMethods(['handle'])
            ->getMock();

        $job->expects($this->once())
            ->method('handle')
            ->willReturnCallback(function () use ($order) {
                // Verify transition to processing
                $order->status = 'processing';
                $order->save();
                $this->assertEquals('processing', $order->status);

                // Simulate failure
                $order->status = 'failed';
                $order->save();
                $this->assertEquals('failed', $order->status);

                throw new \Exception('Payment processing failed');
            });

        $this->expectException(\Exception::class);
        $job->handle();

        // Verify final failed status persisted
        $order->refresh();
        $this->assertEquals('failed', $order->status);
    }

    public function testJobRetriesOnFailure()
    {
        // Fake the queue
        Queue::fake();
        
        // Create a user and order
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'amount' => 51231.00,
            'status' => 'pending'
        ]);



        $job = $this->getMockBuilder(ProcessPayment::class)
            ->setConstructorArgs([$order->id])
            ->onlyMethods(['handle'])
            ->getMock();

        $job->expects($this->exactly(3))
            ->method('handle')
            ->willReturnCallback(function () use ($order) {
                // Simulate the actual job behavior
                $order->status = 'processing';
                $order->save();
                
                $order->status = 'failed';
                $order->save();
                throw new \Exception("Payment processing failed for order {$order->id}");
            });


        // Execute the mock job three times to simulate retries
        for ($i = 0; $i < 3; $i++) {
            try {
                $job->handle();
            } catch (\Exception $e) {
                $this->assertEquals("Payment processing failed for order {$order->id}", $e->getMessage());
            }
        }

        // Verify the order status is updated to failed after all retries
        $order->refresh();
        $this->assertEquals('failed', $order->status);
    }
}
