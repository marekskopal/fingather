import {
    ChangeDetectionStrategy, Component, input
} from '@angular/core';
import { AssetsWithProperties, Currency } from '@app/models';


@Component({
    selector: 'fingather-closed-assets',
    templateUrl: 'closed-assets.component.html',
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
