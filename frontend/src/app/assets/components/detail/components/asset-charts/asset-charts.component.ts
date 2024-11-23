import {
    ChangeDetectionStrategy,
    Component, input,
} from '@angular/core';
import {
    AssetTickerChartComponent,
} from "@app/assets/components/detail/components/asset-charts/components/asset-ticker-chart/asset-ticker-chart.component";
import {
    AssetValueChartComponent,
} from "@app/assets/components/detail/components/asset-charts/components/asset-value-chart/asset-value-chart.component";
import {AssetChartsTabEnum} from "@app/assets/components/detail/components/asset-charts/types/asset-charts-tab-enum";
import { AssetWithProperties} from '@app/models';
import {NgbNav, NgbNavContent, NgbNavItem, NgbNavLinkButton, NgbNavOutlet} from "@ng-bootstrap/ng-bootstrap";

@Component({
    templateUrl: 'asset-charts.component.html',
    selector: 'fingather-asset-charts',
    imports: [
        NgbNav,
        NgbNavItem,
        NgbNavLinkButton,
        NgbNavContent,
        AssetTickerChartComponent,
        AssetValueChartComponent,
        NgbNavOutlet,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AssetChartsComponent {
    public readonly $asset = input.required<AssetWithProperties>({
        alias: 'asset',
    });

    protected activeTab: AssetChartsTabEnum = AssetChartsTabEnum.AssetValueChart;
    protected readonly AssetChartsTabEnum = AssetChartsTabEnum;

    protected get asset(): AssetWithProperties {
        return this.$asset();
    }
}
