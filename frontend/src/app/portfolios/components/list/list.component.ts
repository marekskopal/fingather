import {
    ChangeDetectionStrategy, Component, OnDestroy, OnInit
} from '@angular/core';
import { Portfolio } from '@app/models';
import { AddEditComponent } from '@app/portfolios/components/add-edit/add-edit.component';
import { PortfolioService } from '@app/services';
import { ConfirmDialogService } from '@app/services/confirm-dialog.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
    templateUrl: 'list.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ListComponent implements OnInit, OnDestroy {
    public portfolios: Portfolio[] | null = null;
    public currentPortfolio: Portfolio;

    public constructor(
        private readonly portfolioService: PortfolioService,
        private readonly modalService: NgbModal,
        private readonly confirmDialogService: ConfirmDialogService,
    ) {}

    public async ngOnInit(): Promise<void> {
        this.currentPortfolio = await this.portfolioService.getCurrentPortfolio();

        this.refreshPortfolios();

        this.portfolioService.eventEmitter.subscribe(() => {
            this.refreshPortfolios();
        });
    }

    public ngOnDestroy(): void {
        this.portfolioService.eventEmitter.unsubscribe();
    }

    public async refreshPortfolios(): Promise<void> {
        this.portfolios = await this.portfolioService.getPortfolios();
    }

    public addPortfolio(): void {
        this.modalService.open(AddEditComponent);
    }

    public editPortfolio(id: number): void {
        const addEditComponent = this.modalService.open(AddEditComponent);
        addEditComponent.componentInstance.id.set(id);
    }

    public async deletePortfolio(id: number): Promise<void> {
        const portfolio = this.portfolios?.find((x) => x.id === id);
        if (portfolio === undefined) {
            return;
        }
        portfolio.isDeleting = true;

        try {
            const confirmed = await this.confirmDialogService.confirm(
                `Delete portfolio ${portfolio.name}`,
                `Are you sure to delete portfolio ${portfolio.name}?`
            );
            if (!confirmed) {
                portfolio.isDeleting = false;
                return;
            }
        } catch (err) {
            portfolio.isDeleting = false;
            return;
        }

        await this.portfolioService.deletePortfolio(id);
        this.portfolios = this.portfolios !== null
            ? this.portfolios.filter((x) => x.id !== id)
            : null;
    }
}
