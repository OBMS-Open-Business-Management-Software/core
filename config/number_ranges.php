<?php

declare(strict_types=1);

use App\Models\Accounting\Contract\Contract;
use App\Models\Accounting\Invoice\Invoice;
use App\Models\Accounting\Invoice\InvoiceReminder;
use App\Models\Shop\OrderQueue\ShopOrderQueue;
use App\Models\User;

return [

    Contract::class => [
        'date' => [
            'prepend' => true,
            'format'  => 'Ymd',
        ],
        'increment' => [
            'group_by' => 'day',
            'reserved' => 0,
        ],
        'prefix' => 'C',
    ],
    Invoice::class => [
        'date' => [
            'prepend' => true,
            'format'  => 'Ymd',
        ],
        'increment' => [
            'group_by' => 'day',
            'reserved' => 0,
        ],
        'prefix' => 'I',
    ],
    InvoiceReminder::class => [
        'date' => [
            'prepend' => true,
            'format'  => 'Ymd',
        ],
        'increment' => [
            'group_by' => 'day',
            'reserved' => 0,
        ],
        'prefix' => 'R',
    ],
    ShopOrderQueue::class => [
        'date' => [
            'prepend' => true,
            'format'  => 'Ymd',
        ],
        'increment' => [
            'group_by' => 'day',
            'reserved' => 0,
        ],
        'prefix' => 'O',
    ],
    User::class => [
        'date' => [
            'prepend' => false,
            'format'  => null,
        ],
        'increment' => [
            'group_by' => null,
            'reserved' => 10000,
        ],
        'prefix' => 'U',
    ],

];
