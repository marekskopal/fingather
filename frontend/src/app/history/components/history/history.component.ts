import { Component, OnInit } from '@angular/core';
import { first } from 'rxjs/operators';

import {CurrencyService, GroupWithGroupDataService} from '@app/services';
import {Currency, GroupWithGroupData} from "@app/models";

@Component({ templateUrl: 'history.component.html' })
export class HistoryComponent implements OnInit {
    public groupsWithGroupData: GroupWithGroupData[]|null = null;
    public defaultCurrency: Currency;

    public constructor(
        private groupWithGroupDataService: GroupWithGroupDataService,
        private currencyService: CurrencyService,
    ) {}

    public async ngOnInit(): Promise<void> {
        this.defaultCurrency = await this.currencyService.getDefaultCurrency();

        this.groupWithGroupDataService.getGroupWithGroupData()
            .pipe(first())
            .subscribe((groupsWithGroupData: GroupWithGroupData[]) => this.groupsWithGroupData = groupsWithGroupData);
    }
}
