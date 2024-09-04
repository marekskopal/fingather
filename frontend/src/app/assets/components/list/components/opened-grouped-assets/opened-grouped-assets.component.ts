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
import {CurrencyPipe} from "@app/shared/pipes/currency.pipe";
import {TranslateModule} from "@ngx-translate/core";


@Component({
    selector: 'fingather-opened-grouped-assets',
    templateUrl: 'opened-grouped-assets.component.html',
    standalone: true,
    imports: [
        TranslateModule,
        MatIcon,
        TickerLogoComponent,
        DecimalPipe,
        CurrencyPipe,
        AsyncPipe,
        ColoredValueDirective,
        RouterLink
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

    protected changeAssetsOrder(orderBy: AssetsOrder): void {
        this.onChangeAssetsOrder$.emit(orderBy);
    }

    protected readonly AssetsOrder = AssetsOrder;
}
