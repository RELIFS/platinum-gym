const CHART_ID = 'admin-operational-trend-chart';
const DATA_ID = 'admin-operational-trend-data';

const palette = {
    gold: '#FEAC18',
    sky: '#38BDF8',
    emerald: '#10B981',
};

const escapeHtml = (value) =>
    String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

const isDarkMode = () => document.documentElement.classList.contains('dark');

const chartTheme = () => {
    const dark = isDarkMode();

    return {
        mode: dark ? 'dark' : 'light',
        foreground: dark ? '#D4D4D8' : '#52525B',
        grid: dark ? 'rgba(255,255,255,0.10)' : 'rgba(113,113,122,0.20)',
        tooltipBackground: dark ? '#18181B' : '#FFFFFF',
        tooltipBorder: dark ? 'rgba(255,255,255,0.12)' : 'rgba(212,212,216,0.95)',
        tooltipTitle: dark ? '#FAFAFA' : '#18181B',
        tooltipText: dark ? '#D4D4D8' : '#3F3F46',
    };
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

const makeSeries = (trend) =>
    (trend.series ?? []).map((item, index) => ({
        name: item.name ?? '-',
        type: index === 0 ? 'area' : 'line',
        data: item.values ?? [],
    }));

const makeOptions = (trend) => {
    const theme = chartTheme();
    const colors = (trend.series ?? []).map((item) => item.color ?? palette[item.tone] ?? palette.gold);

    return {
        chart: {
            type: 'line',
            height: '100%',
            fontFamily: 'Poppins, ui-sans-serif, system-ui, sans-serif',
            parentHeightOffset: 0,
            toolbar: { show: false },
            zoom: { enabled: false },
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 550,
                animateGradually: { enabled: true, delay: 80 },
                dynamicAnimation: { enabled: true, speed: 300 },
            },
            background: 'transparent',
        },
        colors,
        theme: { mode: theme.mode },
        series: makeSeries(trend),
        stroke: {
            curve: 'smooth',
            width: [3.5, 2.5, 2.5],
            lineCap: 'round',
        },
        fill: {
            type: ['gradient', 'solid', 'solid'],
            opacity: [0.42, 1, 1],
            gradient: {
                shade: isDarkMode() ? 'dark' : 'light',
                type: 'vertical',
                shadeIntensity: 0.1,
                opacityFrom: 0.58,
                opacityTo: 0.02,
                stops: [0, 85, 100],
            },
        },
        markers: {
            size: 0,
            strokeWidth: 3,
            hover: { size: 6 },
        },
        dataLabels: { enabled: false },
        legend: { show: false },
        xaxis: {
            categories: trend.labels ?? [],
            labels: {
                rotate: 0,
                trim: true,
                hideOverlappingLabels: true,
                style: {
                    colors: theme.foreground,
                    fontSize: '11px',
                    fontWeight: 700,
                },
            },
            axisBorder: { show: false },
            axisTicks: { show: false },
            tooltip: { enabled: false },
        },
        yaxis: {
            min: 0,
            max: Math.max(1, Number(trend.maxValue ?? 1)),
            tickAmount: 3,
            decimalsInFloat: 0,
            labels: {
                style: {
                    colors: theme.foreground,
                    fontSize: '11px',
                    fontWeight: 700,
                },
                formatter: (value) => Math.round(value).toString(),
            },
        },
        grid: {
            borderColor: theme.grid,
            strokeDashArray: 4,
            xaxis: { lines: { show: false } },
            yaxis: { lines: { show: true } },
            padding: {
                top: 8,
                right: 12,
                bottom: 0,
                left: 4,
            },
        },
        tooltip: {
            theme: theme.mode,
            shared: true,
            intersect: false,
            followCursor: true,
            custom: ({ dataPointIndex, w }) => {
                const label = trend.labels?.[dataPointIndex] ?? trend.period ?? '';
                const rows = (trend.series ?? [])
                    .map((item, index) => {
                        const value = item.values?.[dataPointIndex] ?? 0;
                        const color = item.color ?? colors[index] ?? palette.gold;

                        return `
                            <div class="admin-chart-tooltip-row">
                                <span class="admin-chart-tooltip-dot" style="background-color: ${escapeHtml(color)}"></span>
                                <span class="admin-chart-tooltip-name">${escapeHtml(w.globals.seriesNames[index] ?? item.name ?? '-')}</span>
                                <span class="admin-chart-tooltip-value">${escapeHtml(value)}</span>
                            </div>
                        `;
                    })
                    .join('');

                return `
                    <div class="admin-chart-tooltip" style="background: ${theme.tooltipBackground}; border-color: ${theme.tooltipBorder}; color: ${theme.tooltipText}">
                        <div class="admin-chart-tooltip-title" style="color: ${theme.tooltipTitle}">${escapeHtml(label)}</div>
                        ${rows}
                    </div>
                `;
            },
        },
        states: {
            hover: { filter: { type: 'lighten', value: 0.08 } },
            active: { filter: { type: 'none' } },
        },
        responsive: [
            {
                breakpoint: 640,
                options: {
                    chart: { height: 260 },
                    stroke: { width: [3, 2.25, 2.25] },
                    markers: { hover: { size: 5 } },
                    xaxis: {
                        labels: {
                            showDuplicates: false,
                            style: { fontSize: '10px' },
                        },
                    },
                    grid: {
                        padding: {
                            top: 8,
                            right: 4,
                            bottom: 0,
                            left: -6,
                        },
                    },
                },
            },
        ],
    };
};

export async function initAdminOperationalTrendChart() {
    const element = document.getElementById(CHART_ID);
    const trend = parseTrendPayload();

    if (!element || !trend || trend.isEmpty) {
        return;
    }

    const { default: ApexCharts } = await import('apexcharts');
    const chart = new ApexCharts(element, makeOptions(trend));

    await chart.render();

    const observer = new MutationObserver(() => {
        chart.updateOptions(makeOptions(trend), false, true);
    });

    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
}
