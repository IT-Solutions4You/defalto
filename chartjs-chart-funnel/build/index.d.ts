/**
 * chartjs-chart-funnel
 * https://github.com/sgratzl/chartjs-chart-funnel
 *
 * Copyright (c) 2021 Samuel Gratzl <samu@sgratzl.com>
 */

import { ChartType, ScriptableAndArrayOptions, CommonElementOptions, CommonHoverOptions, ScriptableContext, BarElement, BarOptions, CoreChartOptions, ControllerDatasetOptions, CartesianScaleTypeRegistry, BarController, Scale, UpdateMode, Chart, ChartItem, ChartConfiguration } from 'chart.js';

interface TrapezoidElementOptions extends CommonElementOptions, Record<string, unknown> {
    borderWidth: number;
    shrinkAnchor: 'middle' | 'top' | 'bottom' | 'none';
    shrinkFraction: number;
}
interface TrapezoidElementProps {
    x: number;
    y: number;
    width: number;
    height: number;
    base: number;
    horizontal: boolean;
}
declare class TrapezoidElement extends BarElement {
    static readonly id = "trapezoid";
    options: BarOptions & TrapezoidElementOptions;
    static readonly defaults: {
        shrinkAnchor: string;
        shrinkFraction: number;
    };
    static readonly defaultRoutes: {
        [property: string]: string;
    } | undefined;
    align: 'left' | 'right' | 'center';
    next: TrapezoidElement | undefined;
    previous: TrapezoidElement | undefined;
    private getBounds;
    inRange(mouseX: number | null, mouseY: number | null, useFinalPosition: boolean): boolean;
    inXRange(mouseX: number, useFinalPosition: boolean): boolean;
    inYRange(mouseY: number, useFinalPosition: boolean): boolean;
    getCenterPoint(useFinalPosition: boolean): {
        x: number;
        y: number;
    } | {
        x: number;
        y: number;
    } | {
        x: number;
        y: number;
    };
    tooltipPosition(useFinalPosition: boolean): {
        x: number;
        y: number;
    };
    getRange(axis: string): number;
    private computeWayPoints;
    draw(ctx: CanvasRenderingContext2D): void;
}
declare module 'chart.js' {
    interface ElementOptionsByType<TType extends ChartType> {
        trapezoid: ScriptableAndArrayOptions<TrapezoidElementOptions & CommonHoverOptions, ScriptableContext<TType>>;
    }
}

interface FunnelChartOptions {
    align: 'center' | 'left' | 'right';
}
declare class FunnelController extends BarController {
    options: FunnelChartOptions;
    static readonly id: string;
    static readonly defaults: any;
    static readonly overrides: any;
    getMinMax(scale: Scale, canStack?: boolean | undefined): {
        min: number;
        max: number;
    };
    update(mode: UpdateMode): void;
}
interface FunnelControllerDatasetOptions extends ControllerDatasetOptions, ScriptableAndArrayOptions<TrapezoidElementOptions, ScriptableContext<'funnel'>>, ScriptableAndArrayOptions<CommonHoverOptions, ScriptableContext<'funnel'>> {
}
declare module 'chart.js' {
    interface ChartTypeRegistry {
        funnel: {
            chartOptions: FunnelChartOptions & CoreChartOptions<'funnel'>;
            datasetOptions: FunnelControllerDatasetOptions;
            defaultDataPoint: number;
            metaExtensions: Record<string, never>;
            parsedDataType: {
                x: number;
                y: number;
            };
            scales: keyof CartesianScaleTypeRegistry;
        };
    }
}
declare class FunnelChart<DATA extends unknown[] = number[], LABEL = string> extends Chart<'funnel', DATA, LABEL> {
    static id: "funnel";
    constructor(item: ChartItem, config: Omit<ChartConfiguration<'funnel', DATA, LABEL>, 'type'>);
}

declare function pickForegroundColorToBackgroundColor(color: string, blackColor?: string, whiteColor?: string): string;
declare function blues(i: number, n: number): string;

export { FunnelChart, FunnelController, TrapezoidElement, blues, pickForegroundColorToBackgroundColor };
export type { FunnelChartOptions, FunnelControllerDatasetOptions, TrapezoidElementOptions, TrapezoidElementProps };
//# sourceMappingURL=index.d.ts.map
