<?php

return [
    'types' => [
        'vacation_leave' => App\Components\Request\VacationLeaveType::class,
        'sick_leave' => App\Components\Request\SickLeaveType::class,
        'official_business' => App\Components\Request\OfficialBusinessType::class,
        'undertime' => App\Components\Request\UndertimeType::class,
        'overtime' => App\Components\Request\OvertimeType::class
    ]
];
