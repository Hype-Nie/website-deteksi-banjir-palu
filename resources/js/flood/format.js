export const tiers = ["Tinggi", "Sedang", "Rendah"];
export const colors = {
    Tinggi: "#cc6256",
    Sedang: "#d9a333",
    Rendah: "#36856b",
};
export const number = (value, digits = 0) =>
    new Intl.NumberFormat("id-ID", { maximumFractionDigits: digits }).format(
        value,
    );
export const escapeHtml = (value) =>
    String(value).replace(
        /[&<>"']/g,
        (char) =>
            ({
                "&": "&amp;",
                "<": "&lt;",
                ">": "&gt;",
                '"': "&quot;",
                "'": "&#039;",
            })[char],
    );
export function badge(tier) {
    return `<span class="risk-badge risk-${tier.toLowerCase()}"><span></span>${escapeHtml(tier)}</span>`;
}
export function memberships(point) {
    return tiers
        .map(
            (tier) =>
                `<div class="membership-row"><div><span>${tier}</span><strong>${number(point.membership[tier], 2)}%</strong></div><div class="membership-track"><span style="width:${point.membership[tier]}%;background:${colors[tier]}"></span></div></div>`,
        )
        .join("");
}
