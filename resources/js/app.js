import {
    createIcons,
    Waves,
    MapPin,
    Map,
    ChartNoAxesCombined,
    Table2,
    ScanLine,
    BookOpen,
    GraduationCap,
    ChevronRight,
    Menu,
    Download,
    MapPinned,
    SlidersHorizontal,
    Maximize,
    MousePointer2,
    Info,
    ArrowUpRight,
    GitCompareArrows,
    ScatterChart,
    Blend,
    Layers,
    NotebookPen,
    CircleCheck,
    Search,
    SearchX,
    ChevronLeft,
    ChartNoAxesColumnIncreasing,
    Check,
} from "lucide";
const icons = {
    Waves,
    MapPin,
    Map,
    ChartNoAxesCombined,
    Table2,
    ScanLine,
    BookOpen,
    GraduationCap,
    ChevronRight,
    Menu,
    Download,
    MapPinned,
    SlidersHorizontal,
    Maximize,
    MousePointer2,
    Info,
    ArrowUpRight,
    GitCompareArrows,
    ScatterChart,
    Blend,
    Layers,
    NotebookPen,
    CircleCheck,
    Search,
    SearchX,
    ChevronLeft,
    ChartNoAxesColumnIncreasing,
    Check,
};
import { initMap } from "./flood/map";
import { initPrediction } from "./flood/prediction";
const refreshIcons = () =>
    createIcons({ icons, attrs: { "stroke-width": 1.7 } });
refreshIcons();
document.querySelector("#menu-toggle")?.addEventListener("click", (event) => {
    const open = document.body.classList.toggle("menu-open");
    event.currentTarget.setAttribute("aria-expanded", String(open));
});
document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
        document.body.classList.remove("menu-open");
        document
            .querySelector("#menu-toggle")
            ?.setAttribute("aria-expanded", "false");
    }
});
initMap(refreshIcons);
initPrediction(refreshIcons);
