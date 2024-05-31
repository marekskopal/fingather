import {
    ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit, signal
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
    private readonly $portfolios = signal<Portfolio[] | null>(null);
    protected currentPortfolio: Portfolio;

    public constructor(
        private readonly portfolioService: PortfolioService,
        private readonly modalService: NgbModal,
        private readonly changeDetectorRef: ChangeDetectorRef,
    ) {}

    public async ngOnInit(): Promise<void> {
        this.currentPortfolio = await this.portfolioService.getCurrentPortfolio();

        this.refreshPortfolios();

        this.portfolioService.subscribe(() => {
            this.refreshPortfolios();
            this.changeDetectorRef.detectChanges();
        });
    }

    public ngOnDestroy(): void {
        this.portfolioService.unsubscribe();
    }

    protected get portfolios(): Portfolio[] | null {
        return this.$portfolios();
    }

    private async refreshPortfolios(): Promise<void> {
        const portfolios = await this.portfolioService.getPortfolios();
        this.$portfolios.set(portfolios);
    }

    public addPortfolio(): void {
        this.modalService.open(AddEditComponent);
    }

    public editPortfolio(id: number): void {
        const addEditComponent = this.modalService.open(AddEditComponent);
        addEditComponent.componentInstance.id.set(id);
    }

    public async deletePortfolio(id: number): Promise<void> {
        const portfolio = this.$portfolios()?.find((x) => x.id === id);
        if (portfolio === undefined) {
            return;
        }

        await this.portfolioService.deletePortfolio(id);
        this.$portfolios.update((portfolios) => portfolios !== null
            ? portfolios.filter((x) => x.id !== id)
            : null
        );
    }
}
