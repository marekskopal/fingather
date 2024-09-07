import {AsyncPipe, DecimalPipe} from "@angular/common";
import {
    ChangeDetectionStrategy,
    Component, input,
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import { AssetWithProperties, Currency} from '@app/models';
import {ValueIconComponent} from "@app/shared/components/value-icon/value-icon.component";
import {MoneyPipe} from "@app/shared/pipes/money.pipe";
import {TranslateModule} from "@ngx-translate/core";

@Component({
    templateUrl: 'asset-value.component.html',
    selector: 'fingather-asset-value',
    standalone: true,
    imports: [
        MatIcon,
        TranslateModule,
        DecimalPipe,
        MoneyPipe,
        AsyncPipe,
        ValueIconComponent
    ],
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
