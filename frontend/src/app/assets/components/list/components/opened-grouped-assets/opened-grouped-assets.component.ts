import {
    ChangeDetectionStrategy, Component, input, output
} from '@angular/core';
import { Currency, GroupWithGroupData } from '@app/models';
import { AssetsOrder } from '@app/models/enums/assets-order';


@Component({
    selector: 'fingather-opened-grouped-assets',
    templateUrl: 'opened-grouped-assets.component.html',
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
