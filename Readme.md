[![Queue Tests](https://github.com/youssef-jad/queue-payment/actions/workflows/laravel.yml/badge.svg)](https://github.com/youssef-jad/queue-payment/actions/workflows/laravel.yml)

# Laravel Queue Processing System

A simple Laravel app that handles payment processing using queues and Redis. It's built to be reliable and easy to monitor.

## What's Inside

- Payment processing using Laravel queues
- Redis for handling the queue
- Jobs that retry automatically if they fail
- Proper error handling and logging
- A command to check if the queue is healthy "half done"
- An API endpoint to create orders

## Getting Started

### Using Laravel Sail

1. Start everything up:
```bash
./vendor/bin/sail up -d
```

2. Run the migrations:
```bash
./vendor/bin/sail artisan migrate
```

3. Run the tests:
```bash
./vendor/bin/sail artisan test
```

4 - Run horizon queue 
```bash
./vendor/bin/sail artisan horizon
```

5. Check your jobs in Horizon:
```
http://localhost/horizon/
```

5. Test the queue with this curl command to simulate the API call of create order:
```bash
curl -X POST http://localhost/api/create-order \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "user_id": 1,
    "amount": 51231.00
  }'
```

## Future Improvements

- Add batching for failed jobs
- Set up alerts and notifications
- Add more monitoring features

