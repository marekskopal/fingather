import {
    ChangeDetectionStrategy,
    Component, input,
} from '@angular/core';
import {AssetChartsTabEnum} from "@app/assets/components/detail/components/asset-charts/types/asset-charts-tab-enum";
import { AssetWithProperties} from '@app/models';



@Component({
    templateUrl: 'asset-charts.component.html',
    selector: 'fingather-asset-charts',
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
