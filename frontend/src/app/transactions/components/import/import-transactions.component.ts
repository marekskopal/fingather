import {ChangeDetectionStrategy, Component, inject} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {Router, RouterLink} from "@angular/router";
import {ImportComponent} from "@app/shared/components/import/import.component";
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import { TranslatePipe} from "@ngx-translate/core";

@Component({
    templateUrl: 'import-transactions.component.html',
    imports: [
        PortfolioSelectorComponent,
        TranslatePipe,
        RouterLink,
        MatIcon,
        ImportComponent,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ImportTransactionsComponent {
    private readonly router = inject(Router);

    protected async onImportFinish(): Promise<void> {
        this.router.navigate(['../']);
    }
}
