import {
    ChangeDetectionStrategy,
    Component, inject, OnInit,
} from '@angular/core';
import {ReactiveFormsModule, Validators} from '@angular/forms';
import {MatIcon} from '@angular/material/icon';
import {Router, RouterLink} from '@angular/router';
import {Ticker} from '@app/models';
import {TickerService} from '@app/services';
import {ImportMappingService} from '@app/services/import-mapping.service';
import {BaseAddEditForm} from '@app/shared/components/form/base-add-edit-form';
import {InputValidatorComponent} from '@app/shared/components/input-validator/input-validator.component';
import {SaveButtonComponent} from '@app/shared/components/save-button/save-button.component';
import {TickerSearchSelectorComponent} from '@app/shared/components/ticker-search-selector/ticker-search-selector.component';
import {TranslatePipe} from '@ngx-translate/core';

@Component({
    templateUrl: 'edit-import-mapping.component.html',
    imports: [
        TranslatePipe,
        RouterLink,
        MatIcon,
        ReactiveFormsModule,
        InputValidatorComponent,
        SaveButtonComponent,
        TickerSearchSelectorComponent,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class EditImportMappingComponent extends BaseAddEditForm implements OnInit {
    private readonly importMappingService = inject(ImportMappingService);
    private readonly tickerService = inject(TickerService);
    private readonly router = inject(Router);

    public async ngOnInit(): Promise<void> {
        this.loading.set(true);

        this.initializeIdFromRoute();

        this.form = this.formBuilder.group({
            tickerId: [null, Validators.required],
        });

        const id = this.id();
        if (id !== null) {
            const mapping = await this.importMappingService.getImportMapping(id);
            const ticker = await this.tickerService.getTicker(mapping.tickerId);
            this.form.patchValue({tickerId: ticker});
        }

        this.loading.set(false);
    }

    public async onSubmit(): Promise<void> {
        this.submitted.set(true);

        this.alertService.clear();

        if (this.form.invalid) {
            return;
        }

        const id = this.id();
        if (id === null) {
            return;
        }

        this.saving.set(true);
        try {
            const ticker = this.form.value.tickerId as Ticker;
            await this.importMappingService.updateImportMapping(id, ticker.id);

            this.alertService.success('Update successful', {keepAfterRouteChange: true});
            this.importMappingService.notify();
            this.router.navigate([this.routerBackLink()], {relativeTo: this.route});
        } catch (error) {
            if (error instanceof Error) {
                this.alertService.error(error.message);
            }
        } finally {
            this.saving.set(false);
        }
    }
}
