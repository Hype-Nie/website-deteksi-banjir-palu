<?php

namespace Tests\Feature;

use App\Services\Flood\FloodRepository;
use Tests\TestCase;

class FloodApplicationTest extends TestCase
{
    public function test_pages_render_research_data(): void
    {
        foreach (['/', '/perbandingan', '/data-sampel', '/analisis', '/metodologi'] as $url) {
            $this->get($url)->assertOk()->assertSee('Palu');
        }
    }

    public function test_filters_select_the_correct_model_and_transition_points(): void
    {
        $points = app(FloodRepository::class)->points(['method' => 'fcm', 'risk' => 'Sedang', 'transition' => 1]);
        $this->assertNotEmpty($points);
        foreach ($points as $point) {
            $this->assertSame('Sedang', $point['fcm']);
            $this->assertTrue($point['transition']);
            $this->assertLessThan(85, $point['certainty']);
        }
        $this->getJson('/api/titik?method=fcm&risk=Sedang&transition=1')
            ->assertOk()->assertJsonCount(count($points), 'data');
        $this->getJson('/api/titik?method=invalid')->assertUnprocessable();
    }

    public function test_search_pagination_and_empty_results(): void
    {
        $this->get('/data-sampel?q=unmatchable')->assertOk()->assertSee('Tidak ada titik yang cocok');
        $first = $this->get('/data-sampel');
        $first->assertOk()->assertSee('Berikutnya');
        $this->get('/data-sampel?page=2&risk=Tinggi')->assertOk()->assertSee('risk=Tinggi');
    }

    public function test_export_matches_filtered_records(): void
    {
        $points = app(FloodRepository::class)->points(['risk' => 'Tinggi']);
        $response = $this->get('/ekspor?risk=Tinggi');
        $response->assertOk()->assertDownload('kerawanan-banjir-palu.csv');
        $lines = preg_split('/\r?\n/', trim($response->streamedContent()));
        $this->assertCount(count($points) + 1, $lines);
    }

    public function test_prediction_runs_real_models_and_rejects_invalid_input(): void
    {
        $this->postJson('/api/analisis', ['slope' => -1])->assertUnprocessable()
            ->assertJsonValidationErrors(['rainfall', 'elevation', 'slope', 'distance', 'landuse']);
        $response = $this->postJson('/api/analisis', [
            'rainfall' => 750, 'elevation' => 15, 'slope' => 1.5,
            'distance' => 120, 'landuse' => 'Permukiman dan Tempat Kegiatan',
        ]);
        $response->assertOk()->assertJsonPath('data.kmeans', 'Tinggi')->assertJsonPath('data.fcm', 'Tinggi');
        $this->assertEqualsWithDelta(100, array_sum($response->json('data.membership')), 0.001);
    }

    public function test_missing_prepared_data_has_an_actionable_setup_page(): void
    {
        config(['flood.results' => storage_path('app/flood/not-prepared.json')]);
        $this->get('/')->assertStatus(503)->assertSee('flood:prepare');
        $this->get('/data-sampel')->assertStatus(503)->assertSee('flood:prepare');
        $this->getJson('/api/titik')->assertStatus(503)->assertJsonStructure(['message']);
    }
}
