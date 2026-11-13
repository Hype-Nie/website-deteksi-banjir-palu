import { test, expect } from "@playwright/test";

test("map filters, dataset navigation and CSV download", async ({ page }) => {
    const errors = [];
    page.on("pageerror", (error) => errors.push(error.message));
    await page.goto("/");
    await expect(page.locator("#visible-count")).toHaveText("1.423");
    await page
        .getByRole("button", { name: "Fuzzy C-Means", exact: true })
        .click();
    await page.locator("#transition-only").check();
    await expect(page.locator("#visible-count")).toHaveText("82");
    await page.locator('[name="map-risk"][value="Sedang"]').uncheck();
    await page.locator('[name="map-risk"][value="Rendah"]').uncheck();
    await expect(page.locator("#visible-count")).toHaveText("0");
    await expect(page.locator("#map-loading")).toContainText("Tidak ada titik");
    await page.goto("/data-sampel?q=unmatchable");
    await expect(
        page.getByRole("heading", { name: "Tidak ada titik yang cocok" }),
    ).toBeVisible();
    await page.goto("/data-sampel?risk=Tinggi");
    const downloadPromise = page.waitForEvent("download");
    await page.getByRole("link", { name: "Ekspor hasil filter" }).click();
    expect((await downloadPromise).suggestedFilename()).toBe(
        "kerawanan-banjir-palu.csv",
    );
    await page
        .getByRole("link", { name: "Lihat titik", exact: false })
        .first()
        .click();
    await expect(page.locator("#point-detail")).toHaveClass(/has-point/);
    await expect(page.locator("#point-detail")).toContainText(
        "Keanggotaan FCM",
    );
    expect(errors).toEqual([]);
});

test("real model prediction and responsive layouts", async ({ page }) => {
    const errors = [];
    page.on("pageerror", (error) => errors.push(error.message));
    await page.goto("/analisis");
    await page
        .getByRole("button", { name: "Isi contoh dari notebook" })
        .click();
    await page
        .getByRole("button", { name: "Analisis titik", exact: true })
        .click();
    await expect(page.locator("#prediction-output")).toContainText(
        "Hasil analisis titik",
        { timeout: 15000 },
    );
    await expect(page.locator("#prediction-output .risk-badge")).toHaveCount(2);
    await expect(
        page.locator("#prediction-output .risk-badge").first(),
    ).toHaveText("Tinggi");
    for (const width of [1440, 390]) {
        await page.setViewportSize({ width, height: 960 });
        for (const path of [
            "/",
            "/perbandingan",
            "/data-sampel",
            "/analisis",
            "/metodologi",
        ]) {
            await page.goto(path);
            expect(
                await page.evaluate(
                    () =>
                        document.documentElement.scrollWidth <= innerWidth + 1,
                ),
            ).toBeTruthy();
        }
    }
    await page.goto("/");
    await page.getByRole("button", { name: "Buka navigasi" }).click();
    await expect(
        page.getByRole("link", { name: "Perbandingan model", exact: true }),
    ).toBeVisible();
    await page
        .getByRole("link", { name: "Perbandingan model", exact: true })
        .click();
    await expect(
        page.getByRole("heading", { name: "Dua metode. Satu wilayah kajian." }),
    ).toBeVisible();
    expect(errors).toEqual([]);
});
