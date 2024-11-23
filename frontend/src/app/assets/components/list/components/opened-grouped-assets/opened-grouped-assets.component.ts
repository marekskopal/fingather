import {AsyncPipe, DecimalPipe} from "@angular/common";
import {
    ChangeDetectionStrategy, Component, computed, input, output,
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {RouterLink} from "@angular/router";
import { Currency, GroupWithGroupData } from '@app/models';
import { AssetsOrder } from '@app/models/enums/assets-order';
import {TickerLogoComponent} from "@app/shared/components/ticker-logo/ticker-logo.component";
import {ColoredValueDirective} from "@app/shared/directives/colored-value.directive";
import {TableGridDirective} from "@app/shared/directives/table-grid.directive";
import {MoneyPipe} from "@app/shared/pipes/money.pipe";
import {TableGridColumn} from "@app/shared/types/table-grid-column";
import {NumberUtils} from "@app/utils/number-utils";
import {TableUtils} from "@app/utils/table-utils";
import {ScrollShadowDirective} from "@marekskopal/ng-scroll-shadow";
import { TranslatePipe} from "@ngx-translate/core";


@Component({
    selector: 'fingather-opened-grouped-assets',
    templateUrl: 'opened-grouped-assets.component.html',
    imports: [
        TranslatePipe,
        MatIcon,
        TickerLogoComponent,
        DecimalPipe,
        MoneyPipe,
        AsyncPipe,
        ColoredValueDirective,
        RouterLink,
        ScrollShadowDirective,
        TableGridDirective,
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

    protected readonly $tableGridColumns = computed<TableGridColumn[]>(() => {
        let maxDigitsValue = 0;
        let maxDigitsGain = 0;
        let maxDigitsDividend= 0;
        let maxDigitsFxImpact = 0;
        let maxDigitsReturn = 0;

        for (const group of this.$openedGroupedAssets()) {
            for (const asset of group.assets) {
                maxDigitsValue = Math.max(maxDigitsValue, NumberUtils.numberOfDigits(asset.value));
                maxDigitsGain = Math.max(maxDigitsGain, NumberUtils.numberOfDigits(asset.gainDefaultCurrency));
                maxDigitsDividend = Math.max(maxDigitsDividend, NumberUtils.numberOfDigits(asset.dividendYieldDefaultCurrency));
                maxDigitsFxImpact = Math.max(maxDigitsFxImpact, NumberUtils.numberOfDigits(asset.fxImpact));
                maxDigitsReturn = Math.max(maxDigitsReturn, NumberUtils.numberOfDigits(asset.return));
            }
        }

        return [
            { min: '250px', max: '3fr' },
            { min: TableUtils.getTableGridColumnMinWidth(maxDigitsValue), max: '1.2fr' },
            { min: TableUtils.getTableGridColumnMinWidth(maxDigitsGain), max: '1.2fr' },
            { min: TableUtils.getTableGridColumnMinWidth(maxDigitsDividend), max: '1.2fr' },
            { min: TableUtils.getTableGridColumnMinWidth(maxDigitsFxImpact), max: '1.2fr' },
            { min: TableUtils.getTableGridColumnMinWidth(maxDigitsReturn), max: '1.2fr' },
            { min: '65px', max: '1fr' },
        ];
    });

    protected changeAssetsOrder(orderBy: AssetsOrder): void {
        this.onChangeAssetsOrder$.emit(orderBy);
    }

    protected readonly AssetsOrder = AssetsOrder;
}
