import L from "leaflet";
import "leaflet/dist/leaflet.css";
import {
    tiers,
    colors,
    number,
    escapeHtml,
    badge,
    memberships,
} from "./format";

export async function initMap(refreshIcons) {
    const element = document.querySelector("#flood-map");
    if (!element) return;
    const config = JSON.parse(
        document.querySelector("#map-config").textContent,
    );
    const map = L.map(element, {
        preferCanvas: true,
        zoomControl: false,
    }).setView([-0.9, 119.88], 12);
    L.control.zoom({ position: "bottomright" }).addTo(map);
    L.control.scale({ position: "bottomleft", imperial: false }).addTo(map);
    const tiles = L.tileLayer(
        "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
        {
            attribution:
                '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19,
        },
    ).addTo(map);
    let tileError = false;
    tiles.on("tileerror", () => {
        if (tileError) return;
        tileError = true;
        const notice = document.createElement("div");
        notice.className = "tile-notice";
        notice.textContent =
            "Peta dasar belum tersedia. Periksa koneksi internet; titik analisis tetap ditampilkan.";
        element.parentElement.append(notice);
    });
    const layer = L.layerGroup().addTo(map);
    let points = [],
        method = "kmeans",
        selected = null;
    const loading = document.querySelector("#map-loading");
    function showPoint(point) {
        selected = point;
        const panel = document.querySelector("#point-detail");
        panel.classList.add("has-point");
        panel.innerHTML = `<div class="point-title"><h3>Titik #${point.id}</h3><i data-lucide="map-pin"></i></div><p>${escapeHtml(point.landuse)}</p><div class="point-models"><div><span>K-Means</span>${badge(point.kmeans)}</div><div><span>FCM</span>${badge(point.fcm)}</div></div><dl class="point-parameters"><div><dt>Elevasi</dt><dd>${number(point.elevation, 1)} m</dd></div><div><dt>Lereng</dt><dd>${number(point.slope, 1)}&deg;</dd></div><div><dt>Curah hujan</dt><dd>${number(point.rainfall, 1)} mm</dd></div><div><dt>Jarak sungai</dt><dd>${number(point.distance, 1)} m</dd></div></dl><h4>Keanggotaan FCM</h4>${memberships(point)}<p class="point-coordinates">${point.lat.toFixed(5)}, ${point.lng.toFixed(5)}</p>${point.transition ? '<span class="transition-tag">Zona transisi FCM</span>' : ""}`;
        refreshIcons();
    }
    function render() {
        const risks = Array.from(
            document.querySelectorAll('[name="map-risk"]:checked'),
            (input) => input.value,
        );
        const landuse = document.querySelector("#map-landuse").value;
        const transition = document.querySelector("#transition-only").checked;
        const filtered = points.filter(
            (p) =>
                risks.includes(p[method]) &&
                (!landuse || p.landuse === landuse) &&
                (!transition || p.transition),
        );
        layer.clearLayers();
        filtered.forEach((point) => {
            const active = selected?.id === point.id;
            const marker = L.circleMarker([point.lat, point.lng], {
                radius: active ? 8 : 4,
                color: active ? "#183b50" : "#fff",
                weight: active ? 2 : 0.6,
                fillColor: colors[point[method]],
                fillOpacity: 0.86,
            }).addTo(layer);
            marker.bindTooltip(`Titik #${point.id} / ${point[method]}`);
            marker.on("click", () => {
                showPoint(point);
                render();
            });
        });
        document.querySelector("#visible-count").textContent = number(
            filtered.length,
        );
        tiers.forEach((tier) => {
            const count = points.filter((p) => p[method] === tier).length;
            document.querySelector(`#count-${tier.toLowerCase()}`).textContent =
                number(count);
            document.querySelector(
                `#percent-${tier.toLowerCase()}`,
            ).textContent = number((count / points.length) * 100, 1) + "%";
            document.querySelector(`[data-tier="${tier}"]`).textContent =
                number(count);
        });
        const params = new URLSearchParams({ method });
        if (landuse) params.set("landuse", landuse);
        if (transition) params.set("transition", "1");
        const exportLink = document.querySelector("#map-export");
        exportLink.href = `${config.exportUrl}?${params}`;
        exportLink.onclick = (event) => {
            if (risks.length === 3) return;
            event.preventDefault();
            const fields = [
                "id",
                "landuse",
                "rainfall",
                "elevation",
                "slope",
                "distance",
                "lat",
                "lng",
                "kmeans",
                "fcm",
                "certainty",
                "transition",
            ];
            const csv =
                "\uFEFF" +
                [fields, ...filtered.map((p) => fields.map((f) => p[f]))]
                    .map((row) =>
                        row
                            .map(
                                (v) =>
                                    '"' + String(v).replaceAll('"', '""') + '"',
                            )
                            .join(","),
                    )
                    .join("\r\n");
            const url = URL.createObjectURL(
                new Blob([csv], { type: "text/csv;charset=utf-8" }),
            );
            const link = document.createElement("a");
            link.href = url;
            link.download = "kerawanan-banjir-palu.csv";
            link.click();
            URL.revokeObjectURL(url);
        };
        loading.hidden = filtered.length > 0;
        if (!filtered.length)
            loading.textContent =
                "Tidak ada titik yang cocok. Ubah filter untuk menampilkan sampel.";
    }
    document.querySelectorAll("[data-method]").forEach((button) =>
        button.addEventListener("click", () => {
            method = button.dataset.method;
            document.querySelectorAll("[data-method]").forEach((b) => {
                b.classList.toggle("selected", b === button);
                b.setAttribute("aria-pressed", String(b === button));
            });
            render();
        }),
    );
    document
        .querySelectorAll('[name="map-risk"], #map-landuse, #transition-only')
        .forEach((input) => input.addEventListener("change", render));
    document.querySelector("#reset-map").addEventListener("click", () => {
        if (points.length)
            map.fitBounds(
                points.map((p) => [p.lat, p.lng]),
                { padding: [25, 25] },
            );
    });
    try {
        const response = await fetch(config.pointsUrl, {
            headers: { Accept: "application/json" },
        });
        if (!response.ok) throw new Error("Data titik belum dapat dimuat.");
        points = (await response.json()).data;
        map.fitBounds(
            points.map((p) => [p.lat, p.lng]),
            { padding: [25, 25] },
        );
        const focus = points.find(
            (p) => String(p.id) === String(config.pointId),
        );
        if (focus) {
            showPoint(focus);
            map.setView([focus.lat, focus.lng], 15);
        }
        render();
    } catch (error) {
        loading.hidden = false;
        loading.textContent =
            error.message + " Muat ulang halaman untuk mencoba kembali.";
    }
}
