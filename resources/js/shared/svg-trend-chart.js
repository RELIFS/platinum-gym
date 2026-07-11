const SVG_NS = 'http://www.w3.org/2000/svg';

const escapeNumber = (value, fallback = 0) => {
    const number = Number(value);

    return Number.isFinite(number) ? number : fallback;
};

const createSvgElement = (name, attributes = {}) => {
    const element = document.createElementNS(SVG_NS, name);

    Object.entries(attributes).forEach(([key, value]) => {
        element.setAttribute(key, String(value));
    });

    return element;
};

const isDarkMode = () => document.documentElement.classList.contains('dark');

const chartTheme = () => {
    const dark = isDarkMode();

    return {
        foreground: dark ? '#D4D4D8' : '#52525B',
        grid: dark ? 'rgba(255,255,255,0.10)' : 'rgba(113,113,122,0.20)',
        muted: dark ? '#A1A1AA' : '#71717A',
        tooltipBackground: dark ? '#18181B' : '#FFFFFF',
        tooltipBorder: dark ? 'rgba(255,255,255,0.12)' : 'rgba(212,212,216,0.95)',
        tooltipTitle: dark ? '#FAFAFA' : '#18181B',
        tooltipText: dark ? '#D4D4D8' : '#3F3F46',
    };
};

const defaultFormatValue = (value, format) => {
    if (format === 'money') {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0,
        }).format(escapeNumber(value));
    }

    return new Intl.NumberFormat('id-ID').format(escapeNumber(value));
};

const makePoint = (index, value, labels, maxValue, layout) => {
    const xStep = labels.length > 1 ? layout.width / (labels.length - 1) : 0;
    const x = layout.left + xStep * index;
    const yRatio = maxValue > 0 ? escapeNumber(value) / maxValue : 0;
    const y = layout.top + layout.height - yRatio * layout.height;

    return { x, y };
};

const makeLinePath = (points) =>
    points
        .map((point, index) => `${index === 0 ? 'M' : 'L'} ${point.x.toFixed(2)} ${point.y.toFixed(2)}`)
        .join(' ');

const showTooltip = (tooltip, trend, dataPointIndex, formatValue) => {
    const label = trend.labels?.[dataPointIndex] ?? trend.period ?? '';
    const rows = (trend.series ?? [])
        .map((item) => {
            const value = item.values?.[dataPointIndex] ?? 0;

            return `${item.name ?? '-'}: ${formatValue(value, item.format)}`;
        })
        .join(' | ');

    tooltip.textContent = `${label} - ${rows}`;
    tooltip.hidden = false;
};

const hideTooltip = (tooltip) => {
    tooltip.hidden = true;
};

export function renderSvgTrendChart(element, trend, options = {}) {
    if (!element || !trend || trend.isEmpty) {
        return null;
    }

    const palette = options.palette ?? {};
    const formatValue = options.formatValue ?? defaultFormatValue;
    const theme = chartTheme();
    const labels = trend.labels?.length
        ? trend.labels
        : Array.from({ length: Math.max(...(trend.series ?? []).map((item) => item.values?.length ?? 0), 0) }, (_, index) => `${index + 1}`);
    const series = (trend.series ?? []).filter((item) => (item.values ?? []).length > 0);
    const maxValue = Math.max(1, escapeNumber(trend.maxValue, 1), ...series.flatMap((item) => item.values ?? []).map((value) => escapeNumber(value)));
    const layout = { top: 16, right: 18, bottom: 34, left: 42, width: 640, height: 210 };
    const viewBoxWidth = layout.left + layout.width + layout.right;
    const viewBoxHeight = layout.top + layout.height + layout.bottom;

    element.innerHTML = '';
    element.classList.add('relative', 'overflow-hidden');

    const svg = createSvgElement('svg', {
        viewBox: `0 0 ${viewBoxWidth} ${viewBoxHeight}`,
        width: '100%',
        height: '100%',
        role: 'img',
        'aria-label': options.label ?? `Grafik tren ${trend.period ?? ''}`.trim(),
        focusable: 'false',
    });
    svg.style.minHeight = '240px';

    const title = createSvgElement('title');
    title.textContent = options.label ?? `Grafik tren ${trend.period ?? ''}`.trim();
    svg.appendChild(title);

    for (let tick = 0; tick <= 3; tick += 1) {
        const y = layout.top + (layout.height / 3) * tick;
        const value = maxValue - (maxValue / 3) * tick;

        svg.appendChild(createSvgElement('line', {
            x1: layout.left,
            x2: layout.left + layout.width,
            y1: y,
            y2: y,
            stroke: theme.grid,
            'stroke-dasharray': '4 4',
            'stroke-width': 1,
        }));

        const label = createSvgElement('text', {
            x: layout.left - 10,
            y: y + 4,
            'text-anchor': 'end',
            fill: theme.muted,
            'font-size': 11,
            'font-weight': 700,
        });
        label.textContent = formatValue(Math.round(value), 'number');
        svg.appendChild(label);
    }

    labels.forEach((label, index) => {
        if (index % Math.max(1, Math.ceil(labels.length / 6)) !== 0 && index !== labels.length - 1) {
            return;
        }

        const point = makePoint(index, 0, labels, maxValue, layout);
        const text = createSvgElement('text', {
            x: point.x,
            y: layout.top + layout.height + 24,
            'text-anchor': 'middle',
            fill: theme.foreground,
            'font-size': 11,
            'font-weight': 700,
        });
        text.textContent = label;
        svg.appendChild(text);
    });

    const tooltip = document.createElement('div');
    tooltip.className = 'admin-chart-tooltip pointer-events-none absolute bottom-3 left-3 right-3 z-10 rounded-xl border px-3 py-2 text-xs type-compact shadow-lg';
    tooltip.style.background = theme.tooltipBackground;
    tooltip.style.borderColor = theme.tooltipBorder;
    tooltip.style.color = theme.tooltipText;
    tooltip.hidden = true;
    tooltip.setAttribute('aria-live', 'polite');

    series.forEach((item, seriesIndex) => {
        const color = item.color ?? palette[item.tone] ?? palette.gold ?? '#FEAC18';
        const values = labels.map((_, index) => item.values?.[index] ?? 0);
        const points = values.map((value, index) => makePoint(index, value, labels, maxValue, layout));
        const linePath = makeLinePath(points);

        if (seriesIndex === 0 && points.length > 0) {
            const areaPath = `${linePath} L ${points[points.length - 1].x.toFixed(2)} ${(layout.top + layout.height).toFixed(2)} L ${points[0].x.toFixed(2)} ${(layout.top + layout.height).toFixed(2)} Z`;
            svg.appendChild(createSvgElement('path', {
                d: areaPath,
                fill: color,
                opacity: isDarkMode() ? 0.18 : 0.14,
            }));
        }

        svg.appendChild(createSvgElement('path', {
            d: linePath,
            fill: 'none',
            stroke: color,
            'stroke-width': seriesIndex === 0 ? 4 : 2.75,
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
        }));

        points.forEach((point, pointIndex) => {
            const marker = createSvgElement('circle', {
                cx: point.x,
                cy: point.y,
                r: 4,
                fill: color,
                stroke: theme.tooltipBackground,
                'stroke-width': 2,
                tabindex: 0,
                role: 'img',
                'aria-label': `${labels[pointIndex] ?? trend.period ?? ''}: ${item.name ?? '-'} ${formatValue(values[pointIndex], item.format)}`,
            });

            marker.addEventListener('pointerenter', () => showTooltip(tooltip, trend, pointIndex, formatValue));
            marker.addEventListener('focus', () => showTooltip(tooltip, trend, pointIndex, formatValue));
            marker.addEventListener('pointerleave', () => hideTooltip(tooltip));
            marker.addEventListener('blur', () => hideTooltip(tooltip));

            svg.appendChild(marker);
        });
    });

    element.append(svg, tooltip);

    return {
        destroy() {
            element.innerHTML = '';
        },
    };
}
