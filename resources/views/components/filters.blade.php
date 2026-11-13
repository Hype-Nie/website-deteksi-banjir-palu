@props(['filters' => []])
<label class="search-field">
    <i data-lucide="search"></i><input name="q" value="{{ $filters['q'] ?? '' }}"
        placeholder="Cari ID atau penggunaan lahan" aria-label="Cari ID atau penggunaan lahan"></label>
<label class="filter-field"><span>Metode</span><select name="method">
        <option value="kmeans" @selected(($filters['method'] ?? '') === 'kmeans')>K-Means</option>
        <option value="fcm" @selected(($filters['method'] ?? '') === 'fcm')>Fuzzy C-Means</option>
    </select></label>
<label class="filter-field"><span>Kerawanan</span><select name="risk">
        <option value="">Semua tingkat</option>
        @foreach (['Tinggi', 'Sedang', 'Rendah'] as $risk)
            <option @selected(($filters['risk'] ?? '') === $risk)>{{ $risk }}</option>
        @endforeach
    </select></label>
