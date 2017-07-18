<?php

return [
    'mapping' => [
        \App\Notifications\RequestApproved::class => [
            'title' => 'Request Approved', 'content' => 'request_approved'
        ],

        \App\Notifications\RequestDisapproved::class => [
            'title' => 'Request Disapproved', 'content' => 'request_disapproved'
        ],

        \App\Notifications\RequestEscalated::class => [
            'title' => 'Request Escalated', 'content' => 'request_escalated'
        ],

        \App\Notifications\RequestReceived::class => [
            'title' => 'Request Received', 'content' => 'request_received'
        ],

        \App\Notifications\RequestReceivedFromEscalation::class => [
            'title' => 'Request Received From Escalation', 'content' => 'request_received_from_escalation'
        ],
    ]
];
