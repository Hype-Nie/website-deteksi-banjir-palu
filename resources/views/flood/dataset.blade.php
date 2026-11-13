@extends('layouts.app')
@section('title', 'Data sampel')
@section('content')
    <x-page-heading title="Data di balik peta."
        description="Telusuri parameter fisik dan hasil klasterisasi setiap titik sampel.">
        <a class="button button-primary" href="{{ route('export', request()->except('page')) }}"><i data-lucide="download"></i>
            Ekspor hasil filter</a></x-page-heading>
    <div class="dataset-summary"><span><strong>{{ $data['meta']['raw_total'] }}</strong> baris sumber</span><span><i
                data-lucide="circle-check"></i><strong>{{ $data['meta']['total'] }}</strong> titik
            valid</span><span>{{ $data['meta']['excluded'] }} baris tidak lengkap dikecualikan</span></div>
    <section class="panel data-panel">
        <form class="data-filters" method="get"><x-filters :filters="$filters" /><label
                class="filter-field landuse-filter"><span>Penggunaan lahan</span><select name="landuse">
                    <option value="">Semua lahan</option>
                    @foreach ($data['meta']['landuses'] as $landuse)
                        <option @selected(($filters['landuse'] ?? '') === $landuse)>{{ $landuse }}</option>
                    @endforeach
                </select>
            </label><label class="checkbox-field"><input type="checkbox" name="transition" value="1"
                    @checked(request('transition'))>Zona transisi</label><button class="button button-primary"
                type="submit">Terapkan</button><a class="text-button" href="{{ route('dataset') }}">Reset</a></form>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>ID titik</th>
                        <th>Penggunaan lahan</th>
                        <th>Elevasi <small>m</small></th>
                        <th>Lereng <small>&deg;</small></th>
                        <th>Hujan <small>mm</small></th>
                        <th>Jarak sungai <small>m</small></th>
                        <th>K-Means</th>
                        <th>FCM</th>
                        <th>Keanggotaan maks.</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($points as $point)
                        <tr>
                            <td><strong>#{{ $point['id'] }}</strong></td>
                            <td class="landuse-cell">{{ $point['landuse'] }}</td>
                            <td>{{ number_format($point['elevation'], 1, ',', '.') }}</td>
                            <td>{{ number_format($point['slope'], 1, ',', '.') }}</td>
                            <td>{{ number_format($point['rainfall'], 1, ',', '.') }}</td>
                            <td>{{ number_format($point['distance'], 1, ',', '.') }}</td>
                            <td><x-risk-badge :risk="$point['kmeans']" /></td>
                            <td><x-risk-badge :risk="$point['fcm']" /></td>
                            <td><span class="certainty-value">{{ number_format($point['certainty'], 1, ',', '.') }}%</span>
                                @if ($point['transition'])
                                    <small class="transition-label">Zona transisi</small>
                                @endif
                            </td>
                            <td><a class="icon-button" aria-label="Lihat titik {{ $point['id'] }} pada peta"
                                    href="{{ route('map', ['titik' => $point['id']]) }}"><i data-lucide="map-pin"></i></a>
                            </td>
                    </tr>@empty<tr>
                            <td colspan="10">
                                <div class="empty-state"><i data-lucide="search-x"></i>
                                    <h2>Tidak ada titik yang cocok</h2>
                                    <p>Ubah pencarian atau reset filter untuk melihat kembali seluruh sampel.</p><a
                                        href="{{ route('dataset') }}" class="button button-secondary">Reset filter</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-row">
            <span>{{ $points->total() ? $points->firstItem() : 0 }}&ndash;{{ $points->lastItem() ?? 0 }} dari
                {{ number_format($points->total(), 0, ',', '.') }} titik</span>
            <nav aria-label="Halaman data"><a
                    class="button button-secondary {{ $points->onFirstPage() ? 'disabled' : '' }}"
                    @if (!$points->onFirstPage()) href="{{ $points->previousPageUrl() }}" @else aria-disabled="true" @endif><i
                        data-lucide="chevron-left"></i> Sebelumnya</a><span>{{ $points->currentPage() }} /
                    {{ $points->lastPage() }}</span><a
                    class="button button-secondary {{ !$points->hasMorePages() ? 'disabled' : '' }}"
                    @if ($points->hasMorePages()) href="{{ $points->nextPageUrl() }}" @else aria-disabled="true" @endif>Berikutnya
                    <i data-lucide="chevron-right"></i></a></nav>
        </div>
    </section>
@endsection
