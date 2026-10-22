<?php
return [
    'hold_minutes'=>(int) env('BOOKING_HOLD_MINUTES',15),
    'webhook_secret'=>env('PAYMENT_WEBHOOK_SECRET',''),
];
