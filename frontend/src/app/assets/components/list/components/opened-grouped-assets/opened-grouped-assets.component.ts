import {AsyncPipe, DecimalPipe} from "@angular/common";
import {
    ChangeDetectionStrategy, Component, input, output
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {RouterLink} from "@angular/router";
import { Currency, GroupWithGroupData } from '@app/models';
import { AssetsOrder } from '@app/models/enums/assets-order';
import {TickerLogoComponent} from "@app/shared/components/ticker-logo/ticker-logo.component";
import {ColoredValueDirective} from "@app/shared/directives/colored-value.directive";
import {TableGridDirective} from "@app/shared/directives/table-grid.directive";
import {MoneyPipe} from "@app/shared/pipes/money.pipe";
import {ScrollShadowDirective} from "@marekskopal/ng-scroll-shadow";
import {TranslateModule} from "@ngx-translate/core";
import {TableGridColumn} from "@app/shared/types/table-grid-column";


@Component({
    selector: 'fingather-opened-grouped-assets',
    templateUrl: 'opened-grouped-assets.component.html',
    standalone: true,
    imports: [
        TranslateModule,
        MatIcon,
        TickerLogoComponent,
        DecimalPipe,
        MoneyPipe,
        AsyncPipe,
        ColoredValueDirective,
        RouterLink,
        ScrollShadowDirective,
        TableGridDirective
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class OpenedGroupedAssetsComponent {
    public readonly $openedGroupedAssets = input.required<GroupWithGroupData[]>({
        alias: 'openedGroupedAssets',
    });
    public readonly $assetsOrder = input.required<AssetsOrder>({
        alias: 'assetsOrder',
    });
    public readonly $showPerAnnum = input.required<boolean>({
        alias: 'showPerAnnum',
    });
    public readonly $defaultCurrency = input.required<Currency>({
        alias: 'defaultCurrency',
    });
    public readonly onChangeAssetsOrder$ = output<AssetsOrder>({
        alias: 'changeAssetsOrder',
    });

    protected readonly tableGridColumns: TableGridColumn[] = [
        { min: '250px', max: '3fr' },
        { min: '106px', max: '1.2fr' },
        { min: '106px', max: '1.2fr' },
        { min: '106px', max: '1.2fr' },
        { min: '106px', max: '1.2fr' },
        { min: '106px', max: '1.2fr' },
        { min: '65px', max: '1fr' },
    ];

    protected changeAssetsOrder(orderBy: AssetsOrder): void {
        this.onChangeAssetsOrder$.emit(orderBy);
    }

    protected readonly AssetsOrder = AssetsOrder;
}
