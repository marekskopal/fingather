import {AsyncPipe, DecimalPipe} from "@angular/common";
import {
    ChangeDetectionStrategy, Component, input
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {RouterLink} from "@angular/router";
import { AssetsWithProperties } from '@app/models';
import {TickerLogoComponent} from "@app/shared/components/ticker-logo/ticker-logo.component";
import {TableGridDirective} from "@app/shared/directives/table-grid.directive";
import {MoneyPipe} from "@app/shared/pipes/money.pipe";
import {TableGridColumn} from "@app/shared/types/table-grid-column";
import {ScrollShadowDirective} from "@marekskopal/ng-scroll-shadow";
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
        MoneyPipe,
        AsyncPipe,
        ScrollShadowDirective,
        TableGridDirective
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class WatchedAssetsComponent {
    public readonly $assets = input.required<AssetsWithProperties | null>({
        alias: 'assets',
    });

    protected readonly tableGridColumns: TableGridColumn[] = [
        { min: '250px', max: '3fr' },
        { min: '106px', max: '1.2fr' },
        { min: '65px', max: '1fr' },
    ];
}
