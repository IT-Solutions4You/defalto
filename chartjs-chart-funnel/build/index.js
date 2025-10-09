/**
 * chartjs-chart-funnel
 * https://github.com/sgratzl/chartjs-chart-funnel
 *
 * Copyright (c) 2021 Samuel Gratzl <samu@sgratzl.com>
 */

import { BarElement, registry, BarController, Chart, CategoryScale, LinearScale } from 'chart.js';
import { merge } from 'chart.js/helpers';
import chroma from 'chroma-js';

function inBetween(v, min, max, delta = 10e-6) {
    return v >= Math.min(min, max) - delta && v <= Math.max(min, max) + delta;
}
function transpose(m) {
    return {
        left: m.top,
        right: m.bottom,
        top: m.left,
        bottom: m.right,
        horizontal: !m.horizontal,
    };
}
class TrapezoidElement extends BarElement {
    constructor() {
        super(...arguments);
        this.align = 'center';
        this.next = undefined;
        this.previous = undefined;
    }
    getBounds(useFinalPosition = false) {
        const { x, y, base, width, height, horizontal } = this.getProps(['x', 'y', 'base', 'width', 'height', 'horizontal'], useFinalPosition);
        if (horizontal) {
            const w = Math.abs(x - base);
            const left = base - (this.align !== 'left' ? w : 0);
            const right = base + (this.align !== 'right' ? w : 0);
            const half = height / 2;
            const top = y - half;
            const bottom = y + half;
            return { left, top, right, bottom, horizontal };
        }
        else {
            const h = Math.abs(y - base);
            const half = width / 2;
            const left = x - half;
            const right = x + half;
            const top = base - (this.align !== 'right' ? h : 0);
            const bottom = base + (this.align !== 'left' ? h : 0);
            return { left, top, right, bottom, horizontal };
        }
    }
    inRange(mouseX, mouseY, useFinalPosition) {
        const bb = this.getBounds(useFinalPosition);
        const inX = mouseX == null || inBetween(mouseX, bb.left, bb.right);
        const inY = mouseY == null || inBetween(mouseY, bb.top, bb.bottom);
        return inX && inY;
    }
    inXRange(mouseX, useFinalPosition) {
        return this.inRange(mouseX, null, useFinalPosition);
    }
    inYRange(mouseY, useFinalPosition) {
        return this.inRange(null, mouseY, useFinalPosition);
    }
    getCenterPoint(useFinalPosition) {
        const { x, y, base, horizontal } = this.getProps(['x', 'y', 'base', 'horizontal'], useFinalPosition);
        const r = {
            center: {
                x: horizontal ? base : x,
                y: horizontal ? y : base,
            },
            left: {
                x: horizontal ? (base + x) / 2 : x,
                y: horizontal ? y : (base + y) / 2,
            },
            right: {
                x: horizontal ? base - (x - base) / 2 : x,
                y: horizontal ? y : base - (y + base) / 2,
            },
        }[this.align];
        return r;
    }
    tooltipPosition(useFinalPosition) {
        return this.getCenterPoint(useFinalPosition);
    }
    getRange(axis) {
        const { width, height } = this.getProps(['width', 'height']);
        return axis === 'x' ? width : height;
    }
    computeWayPoints(useFinalPosition = false) {
        let dir = this.options.shrinkAnchor;
        let shrinkFraction = Math.max(Math.min(this.options.shrinkFraction, 1), 0);
        if (shrinkFraction === 0) {
            dir = 'none';
            shrinkFraction = 1;
        }
        let bounds = this.getBounds(useFinalPosition);
        const hor = bounds.horizontal;
        let nextBounds = this.next && (dir === 'top' || dir === 'middle') ? this.next.getBounds(useFinalPosition) : bounds;
        let prevBounds = this.previous && (dir === 'bottom' || dir === 'middle') ? this.previous.getBounds(useFinalPosition) : bounds;
        if (!hor) {
            bounds = transpose(bounds);
            nextBounds = transpose(nextBounds);
            prevBounds = transpose(prevBounds);
        }
        const hi = Math.floor((bounds.bottom - bounds.top) * (1 - shrinkFraction));
        const hiRest = Math.floor((bounds.bottom - bounds.top - hi) / 2);
        const points = [];
        const rPoints = [];
        if (dir === 'none' || dir === 'top') {
            points.push([bounds.left, bounds.top], [bounds.right, bounds.top]);
        }
        else {
            let pFraction = 1;
            if (dir === 'middle') {
                const pHiRest = Math.floor((prevBounds.bottom - prevBounds.top) * shrinkFraction * 0.5);
                pFraction = hiRest / (pHiRest + hiRest);
            }
            points.push([bounds.left + (prevBounds.left - bounds.left) * pFraction, bounds.top], [bounds.right + (prevBounds.right - bounds.right) * pFraction, bounds.top]);
        }
        if (dir === 'middle') {
            points.push([bounds.right, bounds.top + hiRest]);
            points.push([bounds.right, bounds.bottom - hiRest]);
            rPoints.push([bounds.left, bounds.top + hiRest]);
            rPoints.push([bounds.left, bounds.bottom - hiRest]);
        }
        else if (dir === 'top' && shrinkFraction < 1) {
            points.push([bounds.right, bounds.top + hi]);
            rPoints.push([bounds.left, bounds.top + hi]);
        }
        else if (dir === 'bottom' && shrinkFraction < 1) {
            points.push([bounds.right, bounds.bottom - hi]);
            rPoints.push([bounds.left, bounds.bottom - hi]);
        }
        if (dir === 'none' || dir === 'bottom') {
            points.push([bounds.right, bounds.bottom], [bounds.left, bounds.bottom]);
        }
        else {
            let nFraction = 1;
            if (dir === 'middle') {
                const nHiRest = Math.floor((nextBounds.bottom - nextBounds.top) * shrinkFraction * 0.5);
                nFraction = hiRest / (nHiRest + hiRest);
            }
            points.push([bounds.right + (nextBounds.right - bounds.right) * nFraction, bounds.bottom], [bounds.left + (nextBounds.left - bounds.left) * nFraction, bounds.bottom]);
        }
        points.push(...rPoints.reverse());
        if (!hor) {
            return points.map(([x, y]) => [y, x]);
        }
        return points;
    }
    draw(ctx) {
        const { options } = this;
        ctx.save();
        ctx.beginPath();
        const points = this.computeWayPoints();
        ctx.moveTo(points[0][0], points[0][1]);
        for (const p of points.slice(1)) {
            ctx.lineTo(p[0], p[1]);
        }
        if (options.backgroundColor) {
            ctx.fillStyle = options.backgroundColor;
            ctx.fill();
        }
        if (options.borderColor) {
            ctx.strokeStyle = options.borderColor;
            ctx.lineWidth = options.borderWidth;
            ctx.stroke();
        }
        ctx.restore();
    }
}
TrapezoidElement.id = 'trapezoid';
TrapezoidElement.defaults = {
    ...BarElement.defaults,
    shrinkAnchor: 'top',
    shrinkFraction: 1,
};
TrapezoidElement.defaultRoutes = BarElement.defaultRoutes;

function pickForegroundColorToBackgroundColor(color, blackColor = '#000000', whiteColor = '#ffffff') {
    return chroma(color).luminance() > 0.5 ? blackColor : whiteColor;
}
function blues(i, n) {
    return chroma
        .scale('Blues')(i / (n - 1))
        .hex();
}

function patchController(type, config, controller, elements = [], scales = []) {
    registry.addControllers(controller);
    if (Array.isArray(elements)) {
        registry.addElements(...elements);
    }
    else {
        registry.addElements(elements);
    }
    if (Array.isArray(scales)) {
        registry.addScales(...scales);
    }
    else {
        registry.addScales(scales);
    }
    const c = config;
    c.type = type;
    return c;
}

class FunnelController extends BarController {
    getMinMax(scale, canStack) {
        const { max } = super.getMinMax(scale, canStack);
        const r = {
            center: { min: -max, max },
            left: { min: 0, max },
            right: { min: -max, max: 0 },
        }[this.options.align];
        return r;
    }
    update(mode) {
        super.update(mode);
        const meta = this._cachedMeta;
        const elements = (meta.data || []);
        for (let i = 0; i < elements.length; i++) {
            elements[i].align = this.options.align;
            elements[i].next = elements[i + 1];
            elements[i].previous = elements[i - 1];
        }
    }
}
FunnelController.id = 'funnel';
FunnelController.defaults = merge({}, [
    BarController.defaults,
    {
        dataElementType: TrapezoidElement.id,
        barPercentage: 1,
        align: 'center',
        categoryPercentage: 0.98,
    },
]);
FunnelController.overrides = merge({}, [
    BarController.overrides,
    {
        plugins: {
            legend: {
                display: false,
            },
            colors: {
                enabled: false,
            },
            datalabels: {
                anchor: 'start',
                textAlign: 'center',
                font: {
                    size: 20,
                },
                color: (context) => {
                    const bgColor = context.chart.getDatasetMeta(context.datasetIndex).data[context.dataIndex].options
                        .backgroundColor;
                    return pickForegroundColorToBackgroundColor(bgColor, Chart.defaults.color, '#ffffff');
                },
                formatter: (value, context) => {
                    var _a, _b;
                    const label = (_b = (_a = context.chart.data.labels) === null || _a === void 0 ? void 0 : _a[context.dataIndex]) !== null && _b !== void 0 ? _b : '';
                    return `${label}\n${(value * 100).toLocaleString()}%`;
                },
            },
        },
        scales: {
            _index_: {
                display: false,
                padding: 10,
                grid: {
                    display: false,
                },
            },
            _value_: {
                display: false,
                beginAtZero: false,
                grace: 0,
                grid: {
                    display: false,
                },
            },
        },
        elements: {
            trapezoid: {
                backgroundColor(context) {
                    const nData = context.chart.data.datasets[context.datasetIndex].data.length;
                    return blues(context.dataIndex, nData);
                },
            },
        },
    },
]);
class FunnelChart extends Chart {
    constructor(item, config) {
        super(item, patchController('funnel', config, FunnelController, TrapezoidElement, [CategoryScale, LinearScale]));
    }
}
FunnelChart.id = FunnelController.id;

export { FunnelChart, FunnelController, TrapezoidElement, blues, pickForegroundColorToBackgroundColor };
//# sourceMappingURL=index.js.map
