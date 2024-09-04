import {AsyncPipe, DecimalPipe} from "@angular/common";
import {
    ChangeDetectionStrategy, Component, input
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {RouterLink} from "@angular/router";
import { AssetsWithProperties } from '@app/models';
import {TickerLogoComponent} from "@app/shared/components/ticker-logo/ticker-logo.component";
import {CurrencyPipe} from "@app/shared/pipes/currency.pipe";
import {TranslateModule} from "@ngx-translate/core";


@Component({
    selector: 'fingather-watched-assets',
    templateUrl: 'watched-assets.component.html',
    standalone: true,
    imports: [
        TranslateModule,
        TickerLogoComponent,
        RouterLink,
        MatIcon,
        DecimalPipe,
        CurrencyPipe,
        AsyncPipe
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class WatchedAssetsComponent {
    public readonly $assets = input.required<AssetsWithProperties | null>({
        alias: 'assets',
    });
}
