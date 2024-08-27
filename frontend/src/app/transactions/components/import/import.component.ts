import {ChangeDetectionStrategy, Component, inject} from '@angular/core';
import {Router} from "@angular/router";

@Component({
    templateUrl: 'import.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ImportComponent {
    private readonly router = inject(Router);

    protected async onImportFinish(): Promise<void> {
        this.router.navigate(['../']);
    }
}
