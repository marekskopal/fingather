import {
    ChangeDetectionStrategy, ChangeDetectorRef, Component, inject, OnInit, signal
} from '@angular/core';
import { Portfolio } from '@app/models';
import { PortfolioService } from '@app/services';

@Component({
    templateUrl: 'list.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ListComponent implements OnInit {
    private readonly portfolioService = inject(PortfolioService);
    private readonly changeDetectorRef = inject(ChangeDetectorRef);

    private readonly $portfolios = signal<Portfolio[] | null>(null);
    protected currentPortfolio: Portfolio;

    public async ngOnInit(): Promise<void> {
        this.currentPortfolio = await this.portfolioService.getCurrentPortfolio();

        this.refreshPortfolios();

        this.portfolioService.subscribe(() => {
            this.refreshPortfolios();
            this.changeDetectorRef.detectChanges();
        });
    }

    protected get portfolios(): Portfolio[] | null {
        return this.$portfolios();
    }

    private async refreshPortfolios(): Promise<void> {
        const portfolios = await this.portfolioService.getPortfolios();
        this.$portfolios.set(portfolios);
    }

    public async deletePortfolio(id: number): Promise<void> {
        const portfolio = this.$portfolios()?.find((x) => x.id === id);
        if (portfolio === undefined) {
            return;
        }

        await this.portfolioService.deletePortfolio(id);
        this.$portfolios.update((portfolios) => (portfolios !== null
            ? portfolios.filter((x) => x.id !== id)
            : null));
    }
}
