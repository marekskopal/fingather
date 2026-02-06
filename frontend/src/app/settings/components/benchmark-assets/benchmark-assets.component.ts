import {ChangeDetectionStrategy, Component, inject, OnInit, signal} from '@angular/core';
import {FormsModule} from '@angular/forms';
import {MatIcon} from '@angular/material/icon';
import {Router} from '@angular/router';
import {BenchmarkAsset} from '@app/models';
import {UserRoleEnum} from '@app/models/enums/user-role-enum';
import {CurrentUserService} from '@app/services';
import {BenchmarkAssetService} from '@app/services/benchmark-asset.service';
import {DeleteButtonComponent} from '@app/shared/components/delete-button/delete-button.component';
import {PortfolioSelectorComponent} from '@app/shared/components/portfolio-selector/portfolio-selector.component';
import {TickerSearchSelectorComponent} from '@app/shared/components/ticker-search-selector/ticker-search-selector.component';
import {ScrollShadowDirective} from '@marekskopal/ng-scroll-shadow';
import {TranslatePipe} from '@ngx-translate/core';

@Component({
    templateUrl: 'benchmark-assets.component.html',
    imports: [
        TranslatePipe,
        PortfolioSelectorComponent,
        MatIcon,
        DeleteButtonComponent,
        ScrollShadowDirective,
        TickerSearchSelectorComponent,
        FormsModule,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class BenchmarkAssetsComponent implements OnInit {
    private readonly benchmarkAssetService = inject(BenchmarkAssetService);
    private readonly currentUserService = inject(CurrentUserService);
    private readonly router = inject(Router);

    protected readonly benchmarkAssets = signal<BenchmarkAsset[]>([]);
    protected selectedTickerId: number | null = null;

    public async ngOnInit(): Promise<void> {
        const currentUser = await this.currentUserService.getCurrentUser();
        if (currentUser.role !== UserRoleEnum.Admin) {
            this.router.navigate(['/']);
            return;
        }

        await this.refreshBenchmarkAssets();
    }

    private async refreshBenchmarkAssets(): Promise<void> {
        const benchmarkAssets = await this.benchmarkAssetService.getAdminBenchmarkAssets();
        this.benchmarkAssets.set(benchmarkAssets);
    }

    protected async addBenchmarkAsset(): Promise<void> {
        if (this.selectedTickerId === null) {
            return;
        }

        await this.benchmarkAssetService.createBenchmarkAsset({tickerId: this.selectedTickerId});
        this.selectedTickerId = null;
        await this.refreshBenchmarkAssets();
    }

    protected async deleteBenchmarkAsset(id: number): Promise<void> {
        await this.benchmarkAssetService.deleteBenchmarkAsset(id);
        this.benchmarkAssets.update((assets) => assets.filter((x) => x.id !== id));
    }
}
