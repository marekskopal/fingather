import {
    ChangeDetectionStrategy, Component, input, OnInit, output, signal,
} from '@angular/core';
import {Asset} from "@app/models";

@Component({
    selector: 'fingather-asset-selector',
    templateUrl: 'asset-selector.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AssetSelectorComponent implements OnInit {
    public readonly $assets = input.required<Asset[]>({
        alias: 'assets',
    });
    public readonly $selectedAssetId = input<number | null>(null, {
        alias: 'selectedAssetId',
    });
    public readonly $placeholder = input<string | null>(null, {
        alias: 'placeholder',
    });
    public readonly onChangeAsset$ = output<Asset>({
        alias: 'onChangeAsset',
    });

    public ngOnInit(): void {
        this.$selectedAsset.set(this.getAssetById(this.$selectedAssetId()));
    }

    protected readonly $selectedAsset = signal<Asset | null>(null);

    protected changeAsset(ticker: Asset): void {
        this.$selectedAsset.set(ticker);

        this.onChangeAsset$.emit(ticker);
    }

    private getAssetById(id: number | null): Asset | null {
        return this.$assets().find((asset) => asset.id === id) ?? null;
    }
}
