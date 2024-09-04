import {ChangeDetectionStrategy, Component, inject} from '@angular/core';
import {Router} from "@angular/router";

@Component({
    templateUrl: 'import-transactions.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ImportTransactionsComponent {
    private readonly router = inject(Router);

    protected async onImportFinish(): Promise<void> {
        this.router.navigate(['../']);
    }
}
