<?php

namespace App\Http\Controllers;

use App\Http\Requests\FloodFilterRequest;
use App\Http\Requests\PredictFloodRequest;
use App\Services\Flood\FloodRepository;
use App\Services\Flood\ModelRunner;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use RuntimeException;

class FloodController extends Controller
{
    public function page(Request $request, FloodRepository $repository)
    {
        try {
            $data = $repository->data();
        } catch (RuntimeException $exception) {
            return response()->view('flood.setup', ['message' => $exception->getMessage()], 503);
        }
        $page = $request->route()->defaults['page'] ?? 'map';

        return view('flood.'.$page, ['data' => $data, 'page' => $page]);
    }

    public function points(FloodFilterRequest $request, FloodRepository $repository)
    {
        return response()->json(['data' => $repository->points($request->validated())]);
    }

    public function dataset(FloodFilterRequest $request, FloodRepository $repository)
    {
        $filters = $request->validated();
        $all = $repository->points($filters);
        $page = (int) ($filters['page'] ?? 1);
        $points = new LengthAwarePaginator(array_slice($all, ($page - 1) * 20, 20), count($all), 20, $page, [
            'path' => $request->url(), 'query' => $request->query(),
        ]);

        return view('flood.dataset', ['data' => $repository->data(), 'page' => 'dataset', 'points' => $points, 'filters' => $filters]);
    }

    public function export(FloodFilterRequest $request, FloodRepository $repository)
    {
        $points = $repository->points($request->validated());

        return response()->streamDownload(function () use ($points) {
            $stream = fopen('php://output', 'w');
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, ['id', 'penggunaan_lahan', 'curah_hujan', 'elevasi', 'lereng', 'jarak_sungai', 'X', 'Y', 'latitude', 'longitude', 'kmeans', 'fcm', 'keanggotaan_tinggi', 'keanggotaan_sedang', 'keanggotaan_rendah', 'kepastian_fcm', 'zona_transisi']);
            foreach ($points as $p) {
                fputcsv($stream, [$p['id'], $p['landuse'], $p['rainfall'], $p['elevation'], $p['slope'], $p['distance'], $p['x'], $p['y'], $p['lat'], $p['lng'], $p['kmeans'], $p['fcm'], $p['membership']['Tinggi'], $p['membership']['Sedang'], $p['membership']['Rendah'], $p['certainty'], $p['transition'] ? 'Ya' : 'Tidak']);
            }
            fclose($stream);
        }, 'kerawanan-banjir-palu.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function predict(PredictFloodRequest $request, ModelRunner $runner)
    {
        try {
            return response()->json(['data' => $runner->run('predict', $request->validated())]);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 503);
        }
    }
}
