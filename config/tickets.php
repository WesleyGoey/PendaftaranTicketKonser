<?php

return [

    // Satu-satunya sumber harga kursi: dipakai halaman dan server.
    'seats' => [
        'regular' => ['label' => 'Regular', 'description' => 'Standard concert access', 'price' => 1000000],
        'premium' => ['label' => 'Premium', 'description' => 'Closer to the stage', 'price' => 2000000],
        'vip' => ['label' => 'VIP', 'description' => 'Premium stage access', 'price' => 3000000],
        'diamond' => ['label' => 'Diamond', 'description' => 'The ultimate experience', 'price' => 4000000],
    ],

];
