<?php

return [
    'images' => [
        'max_size' => env('MAX_IMAGE_SIZE', 5), # Mb
        'avatar_max_size' => env('MAX_AVATAR_SIZE', 2)
    ],
    'file' => [
        'max_size_upload' => env('MAX_SIZE_UPLOAD', 10000), # Kb
    ],
];
