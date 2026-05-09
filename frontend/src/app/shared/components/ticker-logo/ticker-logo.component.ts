import {NgOptimizedImage} from "@angular/common";
import {
    ChangeDetectionStrategy, Component, computed, input, linkedSignal,
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

    protected readonly logoSrc = computed<string | null>(() => {
        const logo = this.ticker().logo;
        return logo !== null ? `images/logos/${logo}` : null;
    });

    /** Resets to false whenever the source URL changes; flipped to true if the image fails to load. */
    protected readonly loadError = linkedSignal<boolean>(() => {
        this.logoSrc();
        return false;
    });

    protected readonly showPlaceholder = computed<boolean>(() => this.logoSrc() === null || this.loadError());

    protected onImageError(): void {
        this.loadError.set(true);
    }
}
