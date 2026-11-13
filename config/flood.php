<?php

return [
    'python' => env('FLOOD_PYTHON', base_path(PHP_OS_FAMILY === 'Windows' ? '.venv/Scripts/python.exe' : '.venv/bin/python')),
    'dataset' => base_path('model/dataset_banjir_palu_final3.csv'),
    'results' => storage_path('app/flood/results.json'),
];
