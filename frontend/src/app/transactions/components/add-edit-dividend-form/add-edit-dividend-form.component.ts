import {ChangeDetectionStrategy, Component, OnInit} from '@angular/core';
import {
    Transaction, TransactionActionType
} from '@app/models';
import {AddEditBaseFormComponent} from "@app/transactions/components/add-edit-base-form/add-edit-base-form.component";

@Component({
    templateUrl: 'add-edit-dividend-form.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AddEditDividendFormComponent extends AddEditBaseFormComponent implements OnInit {
    protected processCreateTransaction(portfolioId: number): Transaction {
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

    protected processUpdateTransaction(id: number): Transaction {
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
