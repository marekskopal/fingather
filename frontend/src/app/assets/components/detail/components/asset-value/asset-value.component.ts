import {AsyncPipe, DecimalPipe} from "@angular/common";
import {
    ChangeDetectionStrategy,
    Component, input,
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import { AssetWithProperties, Currency} from '@app/models';
import {ValueIconComponent} from "@app/shared/components/value-icon/value-icon.component";
import {MoneyPipe} from "@app/shared/pipes/money.pipe";
import { TranslatePipe} from "@ngx-translate/core";

@Component({
    templateUrl: 'asset-value.component.html',
    selector: 'fingather-asset-value',
    imports: [
        MatIcon,
        TranslatePipe,
        DecimalPipe,
        MoneyPipe,
        AsyncPipe,
        ValueIconComponent,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AssetValueComponent {
    public readonly asset = input.required<AssetWithProperties>();
    public readonly defaultCurrency = input.required<Currency>();
}
