import { renderSvgTrendChart } from '../shared/svg-trend-chart';

const CHART_ID = 'admin-operational-trend-chart';
const DATA_ID = 'admin-operational-trend-data';

const palette = {
    gold: '#FEAC18',
    sky: '#38BDF8',
    emerald: '#10B981',
};

const parseTrendPayload = () => {
    const source = document.getElementById(DATA_ID);

    if (!source?.textContent?.trim()) {
        return null;
    }

    try {
        return JSON.parse(source.textContent);
    } catch (error) {
        console.error('[admin-trend] failed to parse chart payload', error);

        return null;
    }
};

export async function initAdminOperationalTrendChart() {
    const element = document.getElementById(CHART_ID);
    const trend = parseTrendPayload();

    if (!element || !trend || trend.isEmpty) {
        return;
    }

    let chart = renderSvgTrendChart(element, trend, {
        palette,
        label: `Grafik tren operasional ${trend.period ?? ''}`.trim(),
    });

    const observer = new MutationObserver(() => {
        chart?.destroy();
        chart = renderSvgTrendChart(element, trend, {
            palette,
            label: `Grafik tren operasional ${trend.period ?? ''}`.trim(),
        });
    });

    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
}
