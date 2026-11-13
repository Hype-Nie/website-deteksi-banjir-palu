@extends('layouts.app')
@section('title', 'Metodologi')
@section('content')
    <x-page-heading title="Dari karakteristik fisik ke peta kerawanan."
        description="Dasar penelitian dan alur pengolahan yang digunakan dalam atlas ini." />
    <div class="methodology-layout">
        <article class="panel research-article"><span class="subtle-badge">Penelitian Teknik Informatika</span>
            <h2>Perbandingan Metode K-Means dan Fuzzy C-Means untuk Analisis Kerawanan Banjir Berbasis Sistem Informasi
                Geografis</h2>
            <p class="research-author">Melsy Patricia Anggelina. E <span>F551 21 026</span><br>Universitas Tadulako</p>
            <p>Wilayah kajian adalah Kota Palu, Sulawesi Tengah. Analisis menggunakan lima parameter fisik untuk
                mengelompokkan titik sampel menjadi tingkat kerawanan tinggi, sedang, dan rendah.</p>
            <h3>Alur pengolahan</h3>
            <ol class="pipeline">
                <li><strong>Pembersihan data</strong>
                    <p>Baris tanpa parameter fisik, koordinat, atau penggunaan lahan yang valid dikecualikan.
                        {{ $data['meta']['total'] }} dari {{ $data['meta']['raw_total'] }} baris dapat dianalisis.</p>
                </li>
                <li><strong>Transformasi dan pembobotan</strong>
                    <p>Penggunaan lahan diberi skor limpasan 1 hingga 5. Quantile Gaussian dan Min-Max dari model tersimpan
                        menormalkan lima fitur sebelum pembobotan hidrologi.</p>
                </li>
                <li><strong>Inferensi dua model</strong>
                    <p>K-Means memilih pusat klaster terdekat. FCM menghitung keanggotaan terhadap tiga pusat klaster dengan
                        fuzzifier m = 2. Model tidak dilatih ulang oleh aplikasi.</p>
                </li>
                <li><strong>Interpretasi dan evaluasi</strong>
                    <p>Label kategori mengikuti pemetaan dalam model, berdasarkan urutan rata-rata elevasi. DBI, Silhouette,
                        FPC, dan kesepakatan dihitung dari hasil inferensi dataset.</p>
                </li>
                <li><strong>Pemetaan titik sampel</strong>
                    <p>Koordinat sumber UTM zona 50S (EPSG:32750), sesuai notebook, ditransformasikan ke WGS 84. Peta
                        menampilkan titik sampel; tidak menginterpolasi batas zona atau batas administrasi.</p>
                </li>
            </ol>
            <h3>Batas interpretasi</h3>
            <p>Analisis ini merupakan klasterisasi tanpa label kejadian banjir. Kategori rendah tidak berarti bebas banjir.
                Pembobotan dan ambang transisi 85% mengikuti notebook penelitian yang disertakan. Aplikasi tidak memasukkan
                data cuaca langsung atau memprediksi waktu terjadinya banjir.</p>
            <p class="source-note">Sumber implementasi: draf proposal dalam folder docs serta notebook, dataset CSV, dan dua
                bundle model dalam folder model.</p>
        </article>
        <aside>
            <section class="panel weight-panel">
                <div class="panel-heading">
                    <h2>Bobot parameter</h2><i data-lucide="chart-no-axes-column-increasing"></i>
                </div>
                @foreach (['Elevasi' => 30, 'Penggunaan lahan' => 30, 'Kemiringan lereng' => 20, 'Curah hujan' => 10, 'Jarak sungai' => 10] as $label => $weight)
                    <div class="weight-row">
                        <div><span>{{ $label }}</span><strong>{{ $weight }}%</strong></div>
                        <div class="weight-track"><span style="width:{{ ($weight / 30) * 100 }}%"></span></div>
                    </div>
                @endforeach
                <p>Total bobot 100%, mengikuti konfigurasi Level 2: AHP Hydrologic pada notebook.</p>
            </section>
            <section class="method-callout"><i data-lucide="layers"></i>
                <h2>Mengenali zona transisi</h2>
                <p>Titik dengan keanggotaan FCM maksimum di bawah 85% ditandai sebagai zona transisi. Wilayah tersebut
                    memiliki karakteristik yang beririsan dengan klaster lain.</p><a class="button button-secondary"
                    href="{{ route('map') }}">Jelajahi peta <i data-lucide="map"></i></a>
            </section>
        </aside>
    </div>
@endsection
