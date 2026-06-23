import { renderSvgTrendChart } from '../shared/svg-trend-chart';

const CHART_ID = 'owner-business-trend-chart';
const DATA_ID = 'owner-business-trend-data';

const palette = {
    gold: '#FEAC18',
    sky: '#38BDF8',
};

const parseTrendPayload = () => {
    const source = document.getElementById(DATA_ID);

    if (!source?.textContent?.trim()) {
        return null;
    }

    try {
        return JSON.parse(source.textContent);
    } catch (error) {
        console.error('[owner-trend] failed to parse chart payload', error);

        return null;
    }
};

export async function initOwnerBusinessTrendChart() {
    const element = document.getElementById(CHART_ID);
    const trend = parseTrendPayload();

    if (!element || !trend || trend.isEmpty) {
        return;
    }

    let chart = renderSvgTrendChart(element, trend, {
        palette,
        label: `Grafik tren pendapatan ${trend.period ?? ''}`.trim(),
    });

    const observer = new MutationObserver(() => {
        chart?.destroy();
        chart = renderSvgTrendChart(element, trend, {
            palette,
            label: `Grafik tren pendapatan ${trend.period ?? ''}`.trim(),
        });
    });

    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
}
