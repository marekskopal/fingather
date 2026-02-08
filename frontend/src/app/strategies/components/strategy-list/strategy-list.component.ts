import {
    ChangeDetectionStrategy, Component, input, output,
} from '@angular/core';
import { MatIcon } from '@angular/material/icon';
import { RouterLink } from '@angular/router';
import { Strategy } from '@app/models';
import { DeleteButtonComponent } from '@app/shared/components/delete-button/delete-button.component';
import { ScrollShadowDirective } from '@marekskopal/ng-scroll-shadow';
import { TranslatePipe } from '@ngx-translate/core';

@Component({
    selector: 'fingather-strategy-list',
    templateUrl: 'strategy-list.component.html',
    imports: [
        TranslatePipe,
        RouterLink,
        MatIcon,
        DeleteButtonComponent,
        ScrollShadowDirective,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class StrategyListComponent {
    public readonly strategies = input<Strategy[] | null>(null);
    public readonly selectedStrategyId = input<number | null>(null);

    public readonly afterSelect = output<number>();
    public readonly afterDelete = output<number>();
}
