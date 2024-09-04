import {NgOptimizedImage} from "@angular/common";
import {
    ChangeDetectionStrategy, Component, input, InputSignal
} from '@angular/core';
import { Ticker } from '@app/models';

@Component({
    selector: 'fingather-ticker-logo',
    templateUrl: 'ticker-logo.component.html',
    standalone: true,
    imports: [
        NgOptimizedImage
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TickerLogoComponent {
    public ticker: InputSignal<Ticker> = input.required<Ticker>();

    public get logoSrc(): string {
        return `/images/logos/${this.ticker().logo}`;
    }
}
