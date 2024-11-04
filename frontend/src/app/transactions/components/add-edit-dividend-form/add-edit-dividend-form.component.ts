import {ChangeDetectionStrategy, Component, OnInit} from '@angular/core';
import {ReactiveFormsModule} from "@angular/forms";
import {MatIcon} from "@angular/material/icon";
import {RouterLink} from "@angular/router";
import {
    Transaction, TransactionActionType
} from '@app/models';
import {DateInputComponent} from "@app/shared/components/date-input/date-input.component";
import {InputValidatorComponent} from "@app/shared/components/input-validator/input-validator.component";
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {SaveButtonComponent} from "@app/shared/components/save-button/save-button.component";
import {SelectComponent} from "@app/shared/components/select/select.component";
import {AddEditBaseFormComponent} from "@app/transactions/components/add-edit-base-form/add-edit-base-form.component";
import { TranslatePipe} from "@ngx-translate/core";

@Component({
    templateUrl: 'add-edit-dividend-form.component.html',
    standalone: true,
    imports: [
        PortfolioSelectorComponent,
        TranslatePipe,
        RouterLink,
        MatIcon,
        ReactiveFormsModule,
        SelectComponent,
        InputValidatorComponent,
        DateInputComponent,
        SaveButtonComponent
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AddEditDividendFormComponent extends AddEditBaseFormComponent implements OnInit {
    protected processCreateTransaction(): Transaction {
        const values = this.form.value;
        values.assetId = parseInt(values.assetId, 10);
        values.actionCreated = (new Date(values.actionCreated)).toJSON();
        values.units = '0';
        values.actionType = TransactionActionType.Dividend;
        values.price = values.price.toString();
        values.tax = values.tax.toString();
        values.fee = values.fee.toString();

        if (values.brokerId === '') {
            values.brokerId = null;
        }

        return values;
    }

    protected processUpdateTransaction(): Transaction {
        const values = this.form.value;
        values.actionCreated = (new Date(values.actionCreated)).toJSON();
        values.units = '0';
        values.actionType = TransactionActionType.Dividend;
        values.price = values.price.toString();
        values.tax = values.tax.toString();
        values.fee = values.fee.toString();

        if (values.brokerId === '') {
            values.brokerId = null;
        }

        return values;
    }
}
