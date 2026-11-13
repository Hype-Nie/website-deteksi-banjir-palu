@extends('layouts.app')
@section('title', 'Perbandingan model')
@section('content')
    <x-page-heading title="Dua metode. Satu wilayah kajian."
        description="Bandingkan kualitas klaster dan sebaran kerawanan pada dataset yang sama.">
        <a class="button button-secondary" href="{{ route('export') }}"><i data-lucide="download"></i> Unduh
            hasil</a></x-page-heading>
    <div class="agreement-banner">
        <div><i data-lucide="git-compare-arrows"></i>
            <div>
                <h2>{{ number_format($data['metrics']['agreement'], 2, ',', '.') }}% kesepakatan antar model</h2>
                <p>Proporsi titik dengan kategori kerawanan yang sama pada K-Means dan FCM.</p>
            </div>
        </div><span>{{ $data['meta']['total'] }} titik dibandingkan</span>
    </div>
    <div class="two-column">
        @foreach (['kmeans' => 'K-Means', 'fcm' => 'Fuzzy C-Means'] as $key => $label)
            <section class="panel model-panel">
                <div class="panel-heading">
                    <div class="model-heading"><span class="model-symbol"><i
                                data-lucide="{{ $key === 'kmeans' ? 'scatter-chart' : 'blend' }}"></i></span>
                        <div>
                            <h2>{{ $label }}</h2>
                            <p>{{ $key === 'kmeans' ? 'Satu label tegas untuk setiap titik' : 'Derajat keanggotaan pada setiap klaster' }}
                            </p>
                        </div>
                    </div><span class="subtle-badge">3 klaster</span>
                </div>
                <div class="model-metrics">
                    <div><span>Davies-Bouldin
                            Index</span><strong>{{ number_format($data['metrics'][$key]['dbi'], 4, ',', '.') }}</strong><small>Semakin
                            rendah semakin baik</small></div>
                    <div><span>Silhouette
                            Score</span><strong>{{ number_format($data['metrics'][$key]['silhouette'], 4, ',', '.') }}</strong><small>Semakin
                            tinggi semakin baik</small></div>
                </div>
                <h3>Distribusi tingkat kerawanan</h3>
                <div class="distribution-bar">
                    @foreach (['Tinggi', 'Sedang', 'Rendah'] as $tier)
                        <span class="{{ strtolower($tier) }}"
                            style="width:{{ ($data['metrics'][$key]['counts'][$tier] / $data['meta']['total']) * 100 }}%"
                            title="{{ $tier }}"></span>
                    @endforeach
                </div>
                <div class="distribution-labels">
                    @foreach (['Tinggi', 'Sedang', 'Rendah'] as $tier)
                        <div><span
                                class="risk-dot {{ strtolower($tier) }}"></span><span>{{ $tier }}</span><strong>{{ number_format($data['metrics'][$key]['counts'][$tier], 0, ',', '.') }}</strong>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
    <div class="two-column secondary-panels">
        <section class="panel">
            <div class="panel-heading">
                <h2>Informasi tambahan dari FCM</h2><i data-lucide="layers"></i>
            </div>
            <div class="fcm-extra">
                <div><strong>{{ number_format($data['metrics']['fpc'], 4, ',', '.') }}</strong><span>Fuzzy Partition
                        Coefficient</span></div>
                <div><strong>{{ $data['metrics']['transitions'] }}</strong><span>Titik di zona transisi</span></div>
            </div>
            <p>Zona transisi memiliki keanggotaan maksimum di bawah 85%. Nilai ini menggambarkan batas antar klaster, bukan
                probabilitas terjadinya banjir.</p>
        </section>
        <section class="panel reading-guide"><i data-lucide="notebook-pen"></i>
            <h2>Membaca hasil dengan tepat</h2>
            <p>DBI dan Silhouette mengevaluasi struktur klaster tanpa label kejadian banjir. Kesepakatan kedua metode juga
                tidak sama dengan akurasi prediksi.</p>
            <p>Kategori kerawanan mengikuti notebook: klaster diurutkan berdasarkan rata-rata elevasi, dari yang terendah
                untuk kategori tinggi.</p><a class="text-button" href="{{ route('methodology') }}">Pelajari metodologi <i
                    data-lucide="chevron-right"></i></a>
        </section>
    </div>
@endsection
