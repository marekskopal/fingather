import {AsyncPipe, DecimalPipe} from "@angular/common";
import {
    ChangeDetectionStrategy, Component, input
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {RouterLink} from "@angular/router";
import { AssetsWithProperties, Currency } from '@app/models';
import {TickerLogoComponent} from "@app/shared/components/ticker-logo/ticker-logo.component";
import {ColoredValueDirective} from "@app/shared/directives/colored-value.directive";
import {MoneyPipe} from "@app/shared/pipes/money.pipe";
import {TranslateModule} from "@ngx-translate/core";


@Component({
    selector: 'fingather-closed-assets',
    templateUrl: 'closed-assets.component.html',
    standalone: true,
    imports: [
        TranslateModule,
        TickerLogoComponent,
        DecimalPipe,
        MoneyPipe,
        AsyncPipe,
        ColoredValueDirective,
        RouterLink,
        MatIcon
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ClosedAssetsComponent {
    public readonly $assets = input.required<AssetsWithProperties | null>({
        alias: 'assets',
    });
    public readonly $defaultCurrency = input.required<Currency>({
        alias: 'defaultCurrency',
    });
}
