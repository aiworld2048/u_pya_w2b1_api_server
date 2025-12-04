<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Notification Server
    |--------------------------------------------------------------------------
    |
    | URL and optional API key for the dedicated Socket.IO notification server.
    |
    */
    'server_url' => env('NOTIFICATION_SERVER_URL', 'https://socket.azm999.com'),

    'server_endpoint' => env('NOTIFICATION_SERVER_ENDPOINT', '/'),

    'server_key' => env('NOTIFICATION_SERVER_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Socket Event Names
    |--------------------------------------------------------------------------
    |
    | Allows us to emit different event names for general notifications and chat
    | messages. By default both reuse the legacy "send_noti" event to remain
    | backward compatible with the current realtime server.
    |
    */
    'events' => [
        'notification' => env('NOTIFICATION_EVENT', 'send_noti'),
        'chat' => env('NOTIFICATION_CHAT_EVENT', env('NOTIFICATION_EVENT', 'send_noti')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Static Recipients
    |--------------------------------------------------------------------------
    |
    | Comma separated list of user IDs that should always receive notifications,
    | regardless of the player initiating the event.
    |
    */
    'static_recipient_ids' => array_values(array_filter(
        array_map(function ($id) {
            $id = (int) trim($id);

            return $id > 0 ? $id : null;
        }, explode(',', (string) env('NOTIFICATION_STATIC_RECIPIENT_IDS', '')))
    )),

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    |
    | Frontend routes that should be opened when the admin clicks a notification.
    |
    */
    'routes' => [
        'deposit' => env('NOTIFICATION_DEPOSIT_ROUTE', '/admin/deposit-requests'),
        'withdraw' => env('NOTIFICATION_WITHDRAW_ROUTE', '/admin/withdraw-requests'),
    ],
];

