<?php

namespace App\Services\Flood;

use RuntimeException;
use Symfony\Component\Process\Process;

class ModelRunner
{
    public function run(string $action, ?array $input = null): array
    {
        $environment = ['OMP_NUM_THREADS' => '1', 'OPENBLAS_NUM_THREADS' => '1'];
        if (PHP_OS_FAMILY === 'Windows') {
            // PHP's web SAPI may omit SystemRoot from Symfony's inherited env.
            // CPython needs it to load Windows cryptographic and numerical DLLs.
            $environment['SystemRoot'] = getenv('SystemRoot') ?: 'C:\\Windows';
        }
        $process = new Process([config('flood.python'), base_path('scripts/flood_cli.py'), $action], base_path(), $environment);
        $process->setTimeout(120);
        if ($input !== null) {
            $process->setInput(json_encode($input, JSON_THROW_ON_ERROR));
        }
        $process->run();
        if (! $process->isSuccessful()) {
            report(new RuntimeException($process->getErrorOutput()));
            throw new RuntimeException('Model belum dapat dijalankan. Periksa instalasi Python dan jalankan php artisan flood:prepare.');
        }

        return json_decode($process->getOutput(), true, 512, JSON_THROW_ON_ERROR);
    }
}
