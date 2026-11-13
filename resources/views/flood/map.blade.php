@extends('layouts.app')
@section('title', 'Peta kerawanan')
@section('content')
    <x-page-heading title="Membaca risiko, mengenali wilayah."
        description="Jelajahi sebaran kerawanan banjir Kota Palu dari dua pendekatan klasterisasi.">
        <a class="button button-secondary" href="{{ route('export') }}" id="map-export"><i data-lucide="download"></i> Ekspor
            data</a></x-page-heading>
    <div class="overview-strip">
        <div class="overview-intro"><span class="overview-symbol"><i data-lucide="map-pinned"></i></span>
            <div><strong>{{ number_format($data['meta']['total'], 0, ',', '.') }}</strong><span>Titik sampel dianalisis</span>
            </div>
        </div>
        @foreach (['Tinggi', 'Sedang', 'Rendah'] as $tier)
            <div class="overview-risk"><span class="risk-dot {{ strtolower($tier) }}"></span>
                <div><span>Kerawanan {{ strtolower($tier) }}</span><strong
                        id="count-{{ strtolower($tier) }}">{{ number_format($data['metrics']['kmeans']['counts'][$tier], 0, ',', '.') }}</strong>
                </div><span class="overview-percent"
                    id="percent-{{ strtolower($tier) }}">{{ round(($data['metrics']['kmeans']['counts'][$tier] / $data['meta']['total']) * 100, 1) }}%</span>
            </div>
        @endforeach
    </div>
    <section class="map-workspace" aria-label="Eksplorasi peta">
        <div class="map-column">
            <div class="map-toolbar">
                <div class="segmented-control" role="group" aria-label="Metode pemetaan"><button class="selected"
                        data-method="kmeans" aria-pressed="true">K-Means</button><button data-method="fcm"
                        aria-pressed="false">Fuzzy C-Means</button></div><button class="text-button" id="reset-map"><i
                        data-lucide="maximize"></i> Seluruh wilayah</button>
            </div>
            <div class="map-frame">
                <div id="flood-map" aria-label="Peta interaktif kerawanan banjir Kota Palu"></div>
                <div class="map-caption"><i data-lucide="map-pin"></i>
                    <div><strong>Kota Palu</strong><span>Sulawesi Tengah, Indonesia</span></div>
                </div>
                <div class="north-arrow" aria-hidden="true"><span>U</span>&#8593;</div>
                <div class="map-loading" id="map-loading">Memuat titik sampel...</div>
            </div>
            <div class="map-bottom"><span><span class="small-dot"></span> <strong
                        id="visible-count">{{ $data['meta']['total'] }}</strong> titik ditampilkan</span><span>Koordinat
                    sumber: UTM 50S <span class="desktop-only">/ WGS 84</span></span></div>
        </div>
        <aside class="map-inspector">
            <div class="inspector-heading"><i data-lucide="sliders-horizontal"></i>
                <h2>Tampilan peta</h2>
            </div>
            <div class="inspector-section">
                <h3>Tingkat kerawanan</h3>
                <p>Pilih kategori untuk memusatkan analisis.</p>
                <div class="risk-toggles">
                    @foreach (['Tinggi', 'Sedang', 'Rendah'] as $tier)
                        <label><input type="checkbox" name="map-risk" value="{{ $tier }}" checked><span
                                class="risk-dot {{ strtolower($tier) }}"></span>{{ $tier }}<span
                                class="toggle-count"
                                data-tier="{{ $tier }}">{{ $data['metrics']['kmeans']['counts'][$tier] }}</span></label>
                    @endforeach
                </div>
            </div>
            <div class="inspector-section"><label class="field-label" for="map-landuse">Penggunaan lahan</label><select
                    id="map-landuse">
                    <option value="">Semua penggunaan lahan</option>
                    @foreach ($data['meta']['landuses'] as $landuse)
                        <option>{{ $landuse }}</option>
                    @endforeach
                </select><label class="transition-toggle"><input id="transition-only" type="checkbox"><span>Hanya zona
                        transisi FCM<small>Keanggotaan maksimum &lt; 85%</small></span></label></div>
            <div class="point-detail" id="point-detail"><span class="detail-symbol"><i
                        data-lucide="mouse-pointer-2"></i></span>
                <h3>Kenali setiap titik</h3>
                <p>Klik titik pada peta untuk melihat parameter fisik dan hasil kedua model.</p>
            </div>
        </aside>
    </section>
    <div class="below-map">
        <div class="insight-note"><i data-lucide="info"></i>
            <div><strong>Peta kerawanan, bukan prakiraan cuaca.</strong>
                <p>Warna titik menunjukkan kelompok karakteristik wilayah. Hasil ini tidak menunjukkan kejadian banjir
                    secara langsung.</p>
            </div>
        </div><a class="comparison-link" href="{{ route('comparison') }}"><span>Bagaimana kedua model berbeda?<strong>Lihat
                    perbandingan model</strong></span><i data-lucide="arrow-up-right"></i></a>
    </div>
@endsection
@push('scripts')
    <script type="application/json" id="map-config">@json(['pointsUrl'=>route('points'), 'exportUrl'=>route('export'), 'pointId'=>request('titik')])</script>
@endpush
