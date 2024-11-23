import {NgOptimizedImage} from "@angular/common";
import {
    ChangeDetectionStrategy, Component, input,
} from '@angular/core';
import { Ticker } from '@app/models';

@Component({
    selector: 'fingather-ticker-logo',
    templateUrl: 'ticker-logo.component.html',
    imports: [
        NgOptimizedImage,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class TickerLogoComponent {
    public ticker = input.required<Ticker>();
    public width = input.required<number>();
    public height = input.required<number>();

    public get logoSrc(): string {
        return `/images/logos/${this.ticker().logo}`;
    }
}
