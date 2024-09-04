import {ChangeDetectionStrategy, Component, OnInit} from '@angular/core';
import {ReactiveFormsModule} from "@angular/forms";
import {MatIcon} from "@angular/material/icon";
import {RouterLink} from "@angular/router";
import {
    Transaction, TransactionActionType,
} from '@app/models';
import {DateInputComponent} from "@app/shared/components/date-input/date-input.component";
import {InputValidatorComponent} from "@app/shared/components/input-validator/input-validator.component";
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {SaveButtonComponent} from "@app/shared/components/save-button/save-button.component";
import {SelectComponent} from "@app/shared/components/select/select.component";
import {TypeSelectComponent} from "@app/shared/components/type-select/type-select.component";
import {SelectItem} from "@app/shared/types/select-item";
import {AddEditBaseFormComponent} from "@app/transactions/components/add-edit-base-form/add-edit-base-form.component";
import {TranslateModule} from "@ngx-translate/core";

@Component({
    templateUrl: 'add-edit-transaction-form.component.html',
    standalone: true,
    imports: [
        PortfolioSelectorComponent,
        TranslateModule,
        RouterLink,
        MatIcon,
        ReactiveFormsModule,
        SelectComponent,
        InputValidatorComponent,
        TypeSelectComponent,
        DateInputComponent,
        SaveButtonComponent
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AddEditTransactionFormComponent extends AddEditBaseFormComponent implements OnInit {
    protected actionTypes: SelectItem<TransactionActionType, TransactionActionType>[] = [
        {key: TransactionActionType.Buy, label: TransactionActionType.Buy},
        {key: TransactionActionType.Sell, label: TransactionActionType.Sell}
    ];

    protected processCreateTransaction(): Transaction {
        const values = this.form.value;
        values.assetId = parseInt(values.assetId, 10);
        values.actionCreated = (new Date(values.actionCreated)).toJSON();
        values.units = values.units.toString();
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
        values.units = values.units.toString();
        values.price = values.price.toString();
        values.tax = values.tax.toString();
        values.fee = values.fee.toString();

        if (values.brokerId === '') {
            values.brokerId = null;
        }

        return values;
    }
}
