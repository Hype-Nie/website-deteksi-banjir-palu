@extends('layouts.app')
@section('title', 'Analisis titik')
@section('content')
    <x-page-heading title="Uji karakteristik sebuah wilayah."
        description="Masukkan lima parameter fisik untuk membandingkan hasil kedua model tersimpan." />
    <div class="prediction-layout">
        <section class="panel">
            <div class="panel-heading">
                <div>
                    <h2>Parameter wilayah</h2>
                    <p>Gunakan satuan yang sama dengan dataset penelitian.</p>
                </div><i data-lucide="sliders-horizontal"></i>
            </div>
            <form id="prediction-form" action="{{ route('predict') }}" method="post">@csrf<div class="form-grid">
                    @foreach ([['rainfall', 'Curah hujan', 'mm', 0, 15000, 750], ['elevation', 'Elevasi', 'm', -500, 9000, 15], ['slope', 'Kemiringan lereng', '°', 0, 90, 1.5], ['distance', 'Jarak dari sungai', 'm', 0, 1000000, 120]] as [$name, $label, $unit, $min, $max, $value])
                        <label class="form-field"><span>{{ $label }}</span>
                            <div class="input-with-unit"><input name="{{ $name }}" type="number" step="any"
                                    min="{{ $min }}" max="{{ $max }}" placeholder="{{ $value }}"
                                    required><span>{{ $unit }}</span></div>
                        </label>
                    @endforeach
                    <label class="form-field full-width">
                        <span>Penggunaan lahan</span><select name="landuse" required>
                            <option value="">Pilih jenis penggunaan lahan</option>
                            @foreach ($data['meta']['landuses'] as $landuse)
                                <option>{{ $landuse }}</option>
                            @endforeach
                        </select></label>
                </div>
                <div class="form-error" id="prediction-error" role="alert" hidden></div>
                <div class="form-actions"><button class="button button-primary" type="submit"><i
                            data-lucide="scan-line"></i> Analisis titik</button><button class="text-button" type="button"
                        id="example-input">Isi contoh dari notebook</button></div>
            </form>
        </section>
        <section class="panel prediction-output" aria-live="polite" aria-atomic="true" id="prediction-output"><span
                class="result-illustration"><i data-lucide="scan-line"></i></span>
            <h2>Hasil analisis muncul di sini</h2>
            <p>Lengkapi parameter wilayah, lalu jalankan analisis untuk melihat kategori kerawanan dan derajat keanggotaan
                FCM.</p>
        </section>
    </div>
    <div class="insight-note prediction-note"><i data-lucide="info"></i>
        <div><strong>Analisis mengikuti pola pada data penelitian.</strong>
            <p>Input di luar rentang data pelatihan dapat menghasilkan transformasi yang jenuh. Derajat keanggotaan
                merupakan ukuran kedekatan terhadap klaster, bukan peluang banjir.</p>
        </div>
    </div>
@endsection
