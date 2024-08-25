import {
    ChangeDetectionStrategy,
    Component, input,
} from '@angular/core';
import { AssetWithProperties, Currency} from '@app/models';



@Component({
    templateUrl: 'asset-value.component.html',
    selector: 'fingather-asset-value',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AssetValueComponent {
    public readonly $asset = input.required<AssetWithProperties>({
        alias: 'asset',
    });
    public readonly $defaultCurrency = input.required<Currency>({
        alias: 'defaultCurrency',
    });

    protected get asset(): AssetWithProperties {
        return this.$asset();
    }

    protected get defaultCurrency(): Currency {
        return this.$defaultCurrency();
    }
}
