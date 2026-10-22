<?php

return [
    'borrowing_limit' => (int) env('LIBRARY_BORROWING_LIMIT', 3),
    'loan_duration_days' => (int) env('LIBRARY_LOAN_DURATION_DAYS', 14),
    'fine_rate_per_day' => (float) env('LIBRARY_FINE_RATE_PER_DAY', 50),
    'currency' => env('LIBRARY_CURRENCY', 'LKR'),
];
