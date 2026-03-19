import {ColorEnum} from "@app/utils/enum/color-enum";
import csLocale from 'apexcharts/dist/locales/cs.json';
import deLocale from 'apexcharts/dist/locales/de.json';
import enLocale from 'apexcharts/dist/locales/en.json';
import esLocale from 'apexcharts/dist/locales/es.json';
import frLocale from 'apexcharts/dist/locales/fr.json';
import {ApexFill, ApexGrid, ApexLocale, ApexTheme, ApexXAxis, ApexYAxis} from "ng-apexcharts";

const APEX_LOCALES: Record<string, ApexLocale> = {
    en: enLocale as ApexLocale,
    cs: csLocale as ApexLocale,
    de: deLocale as ApexLocale,
    fr: frLocale as ApexLocale,
    es: esLocale as ApexLocale,
};

export class ChartUtils {
    public static gradientFill(): ApexFill {
        return {
            type: 'gradient',
            gradient: {
                shade: 'dark',
                shadeIntensity: 0.9,
                inverseColors: false,
                opacityFrom: 0.8,
                opacityTo: 0,
                stops: [0, 90, 100],
            },
        };
    }

    public static grid(): ApexGrid {
        return {
            borderColor: ColorEnum.colorGrayLighter,
            padding: {
                top: 0,
                right: 0,
                bottom: 0,
                left: 9,
            },
        }
    }

    public static colors(count: number = 5): string[] {
        switch (count) {
            case 1:
                return [ColorEnum.colorChart2];
            case 2:
                return [ColorEnum.colorChart2, ColorEnum.colorChart5];
            case 3:
                return [ColorEnum.colorChart2, ColorEnum.colorChart5, ColorEnum.colorChart4];
            default:
                return [
                    ColorEnum.colorChart1,
                    ColorEnum.colorChart2,
                    ColorEnum.colorChart3,
                    ColorEnum.colorChart4,
                    ColorEnum.colorChart5,
                ].slice(0, count);
        }
    }

    public static getColor(number: number): string {
        const colorNumber = number % this.colors().length;

        return this.colors()[colorNumber];
    }

    public static locale(lang: string): { locales: ApexLocale[], defaultLocale: string } {
        const locale = APEX_LOCALES[lang] ?? APEX_LOCALES['en'];
        return {
            locales: [locale],
            defaultLocale: lang,
        };
    }

    public static theme(): ApexTheme {
        return {
            mode: 'dark',
        };
    }

    public static xAxis(showLabels: boolean = true): ApexXAxis {
        return {
            type: 'datetime',
            categories: [],
            labels: {
                show: showLabels,
                style: {
                    colors: ColorEnum.colorGrayLightest,
                    fontSize: '14px',
                    fontFamily: 'Gaist, sans-serif',
                },
            },
            axisBorder: {
                color: ColorEnum.colorGrayLighter,
            },
            axisTicks: {
                color: ColorEnum.colorGrayLighter,
            },
        };
    }

    public static yAxis(showLabels: boolean = true): ApexYAxis {
        return {
            labels: {
                show: showLabels,
                style: {
                    colors: ColorEnum.colorGrayLightest,
                    fontSize: '14px',
                    fontFamily: 'Gaist, sans-serif',
                },
                formatter: (value: number): string | string[] => {
                    return value.toFixed(2);
                },
                padding: 4,
            },
        };
    }
}
