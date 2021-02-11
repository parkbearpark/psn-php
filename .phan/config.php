<?php

return [
    'target_php_version' => '7.4',
    'directory_list' => [
        'src',
        'vendor/guzzlehttp/guzzle/src',
        'vendor/myclabs/php-enum/src',
        'vendor/tustin/haste/src',
        'vendor/nesbot/carbon/src',
        'vendor/wapmorgan/mp3info/src',
    ],

    'exclude_analysis_directory_list' => [
        'vendor/'
    ],
];