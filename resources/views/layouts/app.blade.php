<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description"
        content="Eksplorasi kerawanan banjir Kota Palu melalui perbandingan K-Means dan Fuzzy C-Means berbasis data spasial penelitian.">
    <title>@yield('title', 'Peta kerawanan') | Palu Waspada</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,600;12..96,700&family=Manrope:wght@400;500;600;650;700;800&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <a class="skip-link" href="#main">Lewati navigasi</a>
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="{{ route('map') }}"><span class="brand-mark"><i
                    data-lucide="waves"></i></span><span>Palu<span class="brand-second">Waspada</span></span></a>
        <p class="brand-caption">Atlas kerawanan banjir</p>
        <nav aria-label="Navigasi utama">
            @foreach ([['map', 'map', 'Peta kerawanan'], ['comparison', 'chart-no-axes-combined', 'Perbandingan model'], ['dataset', 'table-2', 'Data sampel'], ['prediction', 'scan-line', 'Analisis titik'], ['methodology', 'book-open', 'Metodologi']] as [$route, $icon, $label])
                <a href="{{ route($route) }}" class="nav-item {{ $page === $route ? 'active' : '' }}"
                    @if ($page === $route) aria-current="page" @endif><i
                        data-lucide="{{ $icon }}"></i>{{ $label }}@if ($page === $route)
                        <span class="nav-dot"></span>
                    @endif
                </a>
            @endforeach
        </nav>
        <div class="sidebar-bottom">
            <div class="research-icon"><i data-lucide="graduation-cap"></i></div><strong>Berbasis penelitian</strong>
            <p>Teknik Informatika<br>Universitas Tadulako</p><a href="{{ route('methodology') }}">Tentang analisis <i
                    data-lucide="chevron-right"></i></a>
        </div>
        <div class="sidebar-location"><i data-lucide="map-pin"></i> Kota Palu, Sulawesi Tengah</div>
    </aside>
    <div class="app-shell">
        <header class="topbar">
            <div class="breadcrumb"><button class="icon-button mobile-menu" id="menu-toggle" aria-label="Buka navigasi"
                    aria-expanded="false"><i data-lucide="menu"></i></button><span>Atlas Palu</span><i
                    data-lucide="chevron-right"></i><strong>@yield('title', 'Peta kerawanan')</strong></div><span
                class="research-status"><span></span> Data penelitian</span>
        </header>
        <main id="main">@yield('content')</main>
        <footer class="page-footer"><span>Palu Waspada <span class="footer-divider">/</span> Sistem informasi geografis
                kerawanan banjir</span><span>K-Means & Fuzzy C-Means</span></footer>
    </div>
    @stack('scripts')
</body>

</html>
