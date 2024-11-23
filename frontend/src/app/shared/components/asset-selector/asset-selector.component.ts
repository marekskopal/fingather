import {
    ChangeDetectionStrategy, Component, input, OnInit, output, signal,
} from '@angular/core';
import {Asset} from "@app/models";
import {NgbDropdown, NgbDropdownItem, NgbDropdownMenu, NgbDropdownToggle} from "@ng-bootstrap/ng-bootstrap";
import {TranslateModule} from "@ngx-translate/core";

@Component({
    selector: 'fingather-asset-selector',
    templateUrl: 'asset-selector.component.html',
    imports: [
        NgbDropdown,
        NgbDropdownToggle,
        NgbDropdownMenu,
        NgbDropdownItem,
        TranslateModule,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AssetSelectorComponent implements OnInit {
    public readonly assets = input.required<Asset[]>();
    public readonly selectedAssetId = input<number | null>(null);
    public readonly placeholder = input<string | null>(null);
    public readonly afterChangeAsset = output<Asset>();

    public ngOnInit(): void {
        this.selectedAsset.set(this.getAssetById(this.selectedAssetId()));
    }

    protected readonly selectedAsset = signal<Asset | null>(null);

    protected changeAsset(ticker: Asset): void {
        this.selectedAsset.set(ticker);

        this.afterChangeAsset.emit(ticker);
    }

    private getAssetById(id: number | null): Asset | null {
        return this.assets().find((asset) => asset.id === id) ?? null;
    }
}
