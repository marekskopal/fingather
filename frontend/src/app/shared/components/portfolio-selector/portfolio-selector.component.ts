import {
    ChangeDetectionStrategy, Component, inject, OnInit, signal,
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {RouterLink} from "@angular/router";
import { Portfolio } from '@app/models';
import { PortfolioService } from '@app/services';
import {NgbDropdown, NgbDropdownItem, NgbDropdownMenu, NgbDropdownToggle} from "@ng-bootstrap/ng-bootstrap";

@Component({
    selector: 'fingather-portfolio-selector',
    templateUrl: 'portfolio-selector.component.html',
    imports: [
        RouterLink,
        MatIcon,
        NgbDropdown,
        NgbDropdownToggle,
        NgbDropdownMenu,
        NgbDropdownItem,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PortfolioSelectorComponent implements OnInit {
    private readonly portfolioService = inject(PortfolioService);

    private readonly $portfolios = signal<Portfolio[] | null>(null);
    private readonly $currentPortfolio = signal<Portfolio | null>(null);

    public async ngOnInit(): Promise<void> {
        const portfolio = await this.portfolioService.getCurrentPortfolio();
        this.$currentPortfolio.set(portfolio);

        const portfolios = await this.portfolioService.getPortfolios();
        this.$portfolios.set(portfolios);
    }

    protected get portfolios(): Portfolio[] | null {
        return this.$portfolios();
    }

    protected get currentPortfolio(): Portfolio | null {
        return this.$currentPortfolio();
    }

    public async changeCurrentPortfolio(portfolioId: number): Promise<void> {
        const portfolio = await this.portfolioService.getPortfolio(portfolioId);
        this.portfolioService.setCurrentPortfolio(portfolio);
        this.$currentPortfolio.set(portfolio);
        this.portfolioService.notify();
    }
}
