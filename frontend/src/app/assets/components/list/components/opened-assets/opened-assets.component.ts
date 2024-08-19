import {
    ChangeDetectionStrategy, ChangeDetectorRef, Component, input, OnInit, output, signal
} from '@angular/core';
import { AddAssetComponent } from '@app/assets/components/add-asset/add-asset.component';
import { AssetsWithProperties, Currency, GroupWithGroupData } from '@app/models';
import { AssetsOrder } from '@app/models/enums/assets-order';
import {
    AssetService, CurrencyService, GroupWithGroupDataService, PortfolioService
} from '@app/services';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
    selector: 'fingather-opened-assets',
    templateUrl: 'opened-assets.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class OpenedAssetsComponent {
    public readonly $assets = input.required<AssetsWithProperties>({
        alias: 'assets',
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
