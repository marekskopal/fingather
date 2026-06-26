import {
    ChangeDetectionStrategy, Component, effect, input, output, signal,
} from '@angular/core';
import {BenchmarkAsset} from "@app/models";
import {NgbDropdown, NgbDropdownItem, NgbDropdownMenu, NgbDropdownToggle} from "@ng-bootstrap/ng-bootstrap";
import {TranslatePipe} from "@ngx-translate/core";

@Component({
    selector: 'fingather-benchmark-asset-selector',
    templateUrl: 'benchmark-asset-selector.component.html',
    imports: [
        NgbDropdown,
        NgbDropdownToggle,
        NgbDropdownMenu,
        NgbDropdownItem,
        TranslatePipe,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class BenchmarkAssetSelectorComponent {
    public readonly benchmarkAssets = input.required<BenchmarkAsset[]>();
    public readonly selectedTickerId = input<number | null>(null);
    public readonly placeholder = input<string | null>(null);
    public readonly afterChangeBenchmarkAsset = output<BenchmarkAsset>();

    protected readonly selectedBenchmarkAsset = signal<BenchmarkAsset | null>(null);

    public constructor() {
        effect(() => {
            this.selectedBenchmarkAsset.set(this.getBenchmarkAssetByTickerId(this.selectedTickerId()));
        });
    }

    protected changeBenchmarkAsset(benchmarkAsset: BenchmarkAsset): void {
        this.selectedBenchmarkAsset.set(benchmarkAsset);

        this.afterChangeBenchmarkAsset.emit(benchmarkAsset);
    }

    private getBenchmarkAssetByTickerId(tickerId: number | null): BenchmarkAsset | null {
        return this.benchmarkAssets().find((ba) => ba.ticker.id === tickerId) ?? null;
    }
}
