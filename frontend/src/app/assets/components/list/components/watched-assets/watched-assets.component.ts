import {
    ChangeDetectionStrategy, Component, input
} from '@angular/core';
import { AssetsWithProperties } from '@app/models';


@Component({
    selector: 'fingather-watched-assets',
    templateUrl: 'watched-assets.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class WatchedAssetsComponent {
    public readonly $assets = input.required<AssetsWithProperties | null>({
        alias: 'assets',
    });
}
