<?php

use App\Services\Flood\ModelRunner;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

Artisan::command('flood:prepare', function (ModelRunner $runner) {
    $this->info('Membaca model tersimpan dan menyiapkan analisis spasial...');
    $data = $runner->run('prepare');
    $path = config('flood.results');
    File::ensureDirectoryExists(dirname($path));
    File::put($path.'.tmp', json_encode($data, JSON_THROW_ON_ERROR));
    File::move($path.'.tmp', $path);
    $this->info($data['meta']['total'].' titik siap. '.$data['meta']['excluded'].' baris tidak lengkap dikecualikan.');
})->purpose('Menyiapkan hasil inferensi dan metrik dari dataset serta model tersimpan');
