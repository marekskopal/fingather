import {ChangeDetectionStrategy, Component, inject, OnInit, signal} from '@angular/core';
import {FormsModule} from '@angular/forms';
import {MatIcon} from '@angular/material/icon';
import {Router} from '@angular/router';
import {ProxyAsset, Ticker} from '@app/models';
import {TickerTypeEnum} from '@app/models/enums/ticker-type-enum';
import {UserRoleEnum} from '@app/models/enums/user-role-enum';
import {CurrentUserService} from '@app/services';
import {ProxyAssetService} from '@app/services/proxy-asset.service';
import {DeleteButtonComponent} from '@app/shared/components/delete-button/delete-button.component';
import {PortfolioSelectorComponent} from '@app/shared/components/portfolio-selector/portfolio-selector.component';
import {TickerSearchSelectorComponent} from '@app/shared/components/ticker-search-selector/ticker-search-selector.component';
import {ScrollShadowDirective} from '@marekskopal/ng-scroll-shadow';
import {TranslatePipe} from '@ngx-translate/core';

@Component({
    templateUrl: 'proxy-assets.component.html',
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
export class ProxyAssetsComponent implements OnInit {
    private readonly proxyAssetService = inject(ProxyAssetService);
    private readonly currentUserService = inject(CurrentUserService);
    private readonly router = inject(Router);

    protected readonly proxyAssets = signal<ProxyAsset[]>([]);
    protected readonly tickerTypes: TickerTypeEnum[] = Object.values(TickerTypeEnum);
    protected selectedTickerType: TickerTypeEnum = TickerTypeEnum.Stock;
    protected selectedTicker: Ticker | null = null;

    public async ngOnInit(): Promise<void> {
        const currentUser = await this.currentUserService.getCurrentUser();
        if (currentUser.role !== UserRoleEnum.Admin) {
            this.router.navigate(['/']);
            return;
        }

        await this.refreshProxyAssets();
    }

    private async refreshProxyAssets(): Promise<void> {
        const proxyAssets = await this.proxyAssetService.getAdminProxyAssets();
        this.proxyAssets.set(proxyAssets);
    }

    protected async addProxyAsset(): Promise<void> {
        if (this.selectedTicker === null) {
            return;
        }

        await this.proxyAssetService.createProxyAsset({
            tickerType: this.selectedTickerType,
            tickerId: this.selectedTicker.id,
        });
        this.selectedTicker = null;
        await this.refreshProxyAssets();
    }

    protected async deleteProxyAsset(id: number): Promise<void> {
        await this.proxyAssetService.deleteProxyAsset(id);
        this.proxyAssets.update((assets) => assets.filter((x) => x.id !== id));
    }
}
