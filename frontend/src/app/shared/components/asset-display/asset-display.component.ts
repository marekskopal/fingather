import { NgOptimizedImage } from '@angular/common';
import {
    ChangeDetectionStrategy,
    Component,
    computed,
    input,
    linkedSignal,
} from '@angular/core';
import { Ticker } from '@app/models';

@Component({
    selector: 'fingather-asset-display',
    templateUrl: 'asset-display.component.html',
    imports: [
        NgOptimizedImage,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AssetDisplayComponent {
    public ticker = input<Ticker | null>(null);
    public tickerSymbol = input<string | null>(null);
    public tickerName = input<string | null>(null);
    public tickerLogo = input<string | null>(null);
    public logoSize = input<number>(46);

    protected readonly symbol = computed<string>(() => this.ticker()?.ticker ?? this.tickerSymbol() ?? '');
    protected readonly name = computed<string>(() => this.ticker()?.name ?? this.tickerName() ?? '');
    protected readonly logo = computed<string | null>(() => this.ticker()?.logo ?? this.tickerLogo() ?? null);

    protected readonly logoSrc = computed<string | null>(() => {
        const file = this.logo();
        return file !== null ? `images/logos/${file}` : null;
    });

    protected readonly placeholderChar = computed<string>(() => this.name().charAt(0));

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
