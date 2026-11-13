import { badge, memberships, number } from "./format";

export function initPrediction(refreshIcons) {
    const form = document.querySelector("#prediction-form");
    if (!form) return;
    document.querySelector("#example-input").addEventListener("click", () => {
        const example = {
            rainfall: 750,
            elevation: 15,
            slope: 1.5,
            distance: 120,
            landuse: "Permukiman dan Tempat Kegiatan",
        };
        Object.entries(example).forEach(
            ([key, value]) => (form.elements[key].value = value),
        );
    });
    form.addEventListener("submit", async (event) => {
        event.preventDefault();
        const button = form.querySelector('[type="submit"]');
        const error = document.querySelector("#prediction-error");
        const output = document.querySelector("#prediction-output");
        button.disabled = true;
        button.textContent = "Menganalisis...";
        error.hidden = true;
        output.setAttribute("aria-busy", "true");
        try {
            const response = await fetch(form.action, {
                method: "POST",
                headers: {
                    Accept: "application/json",
                    "X-CSRF-TOKEN": form.elements._token.value,
                },
                body: new FormData(form),
            });
            const result = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(
                    result.errors
                        ? Object.values(result.errors).flat().join(" ")
                        : response.status === 419
                          ? "Sesi telah berakhir. Muat ulang halaman, lalu coba lagi."
                          : result.message ||
                            "Analisis belum dapat diproses. Coba kembali.",
                );
            }
            const point = result.data;
            output.classList.add("has-result");
            output.innerHTML = `<div class="panel-heading"><h2>Hasil analisis titik</h2><i data-lucide="circle-check"></i></div><div class="result-models"><div><h3>K-Means</h3>${badge(point.kmeans)}<p>Kategori klaster terdekat</p></div><div><h3>Fuzzy C-Means</h3>${badge(point.fcm)}<p>Keanggotaan maks. ${number(point.certainty, 2)}%</p></div></div><h3>Derajat keanggotaan FCM</h3>${memberships(point)}<div class="result-note"><i data-lucide="${point.transition ? "layers" : "check"}"></i><p>${point.transition ? "Zona transisi: keanggotaan maksimum di bawah 85%. Karakteristik titik beririsan dengan lebih dari satu klaster." : "Keanggotaan maksimum mencapai ambang 85%. Titik memiliki karakteristik yang dominan pada satu klaster."}</p></div>`;
        } catch (exception) {
            error.textContent = exception.message;
            error.hidden = false;
        } finally {
            output.setAttribute("aria-busy", "false");
            button.disabled = false;
            button.innerHTML = '<i data-lucide="scan-line"></i> Analisis titik';
            refreshIcons();
        }
    });
}
