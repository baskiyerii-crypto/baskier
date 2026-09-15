<?php

return [
    'max_kb' => (int) env('UPLOAD_MAX_KB', 5120),
    'image_max_kb' => (int) env('UPLOAD_IMAGE_MAX_KB', 2048),
    'daily_quota' => (int) env('UPLOAD_DAILY_QUOTA', 50),
    'allowed_mimes' => [
        'image/jpeg',
        'image/png',
        'image/webp',
        'application/pdf',
        'application/zip',
        'application/x-zip-compressed',
    ],
    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp', 'pdf', 'zip'],
];
