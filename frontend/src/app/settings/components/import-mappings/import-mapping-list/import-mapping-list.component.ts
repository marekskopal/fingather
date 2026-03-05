import {AsyncPipe} from '@angular/common';
import {
    ChangeDetectionStrategy, ChangeDetectorRef, Component, inject, OnInit, signal,
} from '@angular/core';
import {MatIcon} from '@angular/material/icon';
import {RouterLink} from '@angular/router';
import {ImportMappingDetails} from '@app/models/import-mapping-details';
import {PortfolioService} from '@app/services';
import {ImportMappingService} from '@app/services/import-mapping.service';
import {DeleteButtonComponent} from '@app/shared/components/delete-button/delete-button.component';
import {PortfolioSelectorComponent} from '@app/shared/components/portfolio-selector/portfolio-selector.component';
import {CurrencyCodePipe} from '@app/shared/pipes/currency-code.pipe';
import {ScrollShadowDirective} from '@marekskopal/ng-scroll-shadow';
import {TranslatePipe} from '@ngx-translate/core';

@Component({
    templateUrl: 'import-mapping-list.component.html',
    imports: [
        TranslatePipe,
        PortfolioSelectorComponent,
        RouterLink,
        MatIcon,
        DeleteButtonComponent,
        ScrollShadowDirective,
        AsyncPipe,
        CurrencyCodePipe,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ImportMappingListComponent implements OnInit {
    private readonly importMappingService = inject(ImportMappingService);
    private readonly portfolioService = inject(PortfolioService);
    private readonly changeDetectorRef = inject(ChangeDetectorRef);

    public readonly importMappings = signal<ImportMappingDetails[] | null>(null);

    public ngOnInit(): void {
        this.refreshImportMappings();

        this.importMappingService.subscribe(() => {
            this.refreshImportMappings();
            this.changeDetectorRef.detectChanges();
        });

        this.portfolioService.subscribe(() => {
            this.refreshImportMappings();
            this.changeDetectorRef.detectChanges();
        });
    }

    private async refreshImportMappings(): Promise<void> {
        this.importMappings.set(null);

        const portfolio = await this.portfolioService.getCurrentPortfolio();
        const mappings = await this.importMappingService.getImportMappings(portfolio.id);
        this.importMappings.set(mappings);
    }

    protected async deleteImportMapping(id: number): Promise<void> {
        await this.importMappingService.deleteImportMapping(id);

        this.importMappings.update((mappings) => (mappings !== null
            ? mappings.filter((x) => x.id !== id)
            : null));
    }
}
