import { Component, Input } from '@angular/core';
import { Ticker } from '@app/models';

@Component({
    selector: 'fingather-ticker-logo',
    templateUrl: 'ticker-logo.component.html'
})
export class TickerLogoComponent {
    @Input() public ticker: Ticker;

    public get logoSrc(): string {
        return `/images/logos/${this.ticker.logo}`;
    }
}
