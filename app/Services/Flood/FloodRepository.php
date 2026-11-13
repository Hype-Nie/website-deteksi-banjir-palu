<?php

namespace App\Services\Flood;

use App\Exceptions\PreparedDataUnavailable;
use Illuminate\Support\Facades\File;

class FloodRepository
{
    private ?array $data = null;

    public function data(): array
    {
        if ($this->data !== null) {
            return $this->data;
        }
        $path = config('flood.results');
        if (! is_file($path)) {
            throw new PreparedDataUnavailable('Data analisis belum tersedia. Jalankan php artisan flood:prepare terlebih dahulu.');
        }

        return $this->data = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
    }

    public function points(array $filters): array
    {
        $method = $filters['method'] ?? 'kmeans';

        return array_values(array_filter($this->data()['points'], function ($point) use ($filters, $method) {
            return (empty($filters['risk']) || $point[$method] === $filters['risk'])
                && (empty($filters['landuse']) || $point['landuse'] === $filters['landuse'])
                && (empty($filters['transition']) || $point['transition'])
                && (($filters['q'] ?? '') === '' || str_contains(strtolower($point['id'].' '.$point['landuse']), strtolower($filters['q'])));
        }));
    }
}
