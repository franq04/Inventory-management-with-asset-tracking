<?php

return [
    'purchase_order_defaults' => [
        // Update these defaults to match agency policy and supplier terms.
        'place_of_delivery' => env('PO_PLACE_OF_DELIVERY', 'BPI Compound, Macabalan, Cagayan de Oro City, Misamis Oriental'),
        'delivery_term' => env('PO_DELIVERY_TERM', 'Complete delivery within 30 calendar days upon receipt of PO'),
        'payment_term' => env('PO_PAYMENT_TERM', 'Payment upon complete delivery and acceptance'),
        'delivery_days' => (int) env('PO_DELIVERY_DAYS', 30),
    ],
];
