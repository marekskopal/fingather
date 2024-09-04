import {ChangeDetectionStrategy, Component, inject} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {Router, RouterLink} from "@angular/router";
import {ImportComponent} from "@app/shared/components/import/import.component";
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {TranslateModule} from "@ngx-translate/core";

@Component({
    templateUrl: 'import-transactions.component.html',
    standalone: true,
    imports: [
        PortfolioSelectorComponent,
        TranslateModule,
        RouterLink,
        MatIcon,
        ImportComponent
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ImportTransactionsComponent {
    private readonly router = inject(Router);

    protected async onImportFinish(): Promise<void> {
        this.router.navigate(['../']);
    }
}
