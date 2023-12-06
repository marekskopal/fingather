import { Component, OnInit } from '@angular/core';
import { first } from 'rxjs/operators';

import { PortfolioService } from '@app/_services';
import { Portfolio } from "../_models";

@Component({ templateUrl: 'list.component.html' })
export class ListComponent implements OnInit {
    public portfolio: Portfolio|null = null;

    constructor(
        private portfolioService: PortfolioService,
    ) {}

    ngOnInit() {
        this.portfolioService.get()
            .pipe(first())
            .subscribe(portfolio => this.portfolio = portfolio);
    }
}
