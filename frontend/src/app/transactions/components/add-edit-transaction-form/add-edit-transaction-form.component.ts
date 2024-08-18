import {ChangeDetectionStrategy, Component, inject, OnInit} from '@angular/core';
import {
    Transaction, TransactionActionType,
} from '@app/models';
import {AddEditBaseFormComponent} from "@app/transactions/components/add-edit-base-form/add-edit-base-form.component";
import {SelectItem} from "@app/shared/types/select-item";

@Component({
    templateUrl: 'add-edit-transaction-form.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AddEditTransactionFormComponent extends AddEditBaseFormComponent implements OnInit {
    protected actionTypes: SelectItem<TransactionActionType, TransactionActionType>[] = [
        {key: TransactionActionType.Buy, label: TransactionActionType.Buy},
        {key: TransactionActionType.Sell, label: TransactionActionType.Sell}
    ];

    protected processCreateTransaction(portfolioId: number): Transaction {
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

    protected processUpdateTransaction(id: number): Transaction {
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
