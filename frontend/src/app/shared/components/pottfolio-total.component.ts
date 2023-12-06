import { Component, OnInit } from '@angular/core';

import { PortfolioData } from '@app/_models';
import { PortfolioDataService } from '@app/_services';
import {first} from "rxjs/operators";

@Component({ selector: 'portfolio-total', templateUrl: 'portfolio-total.component.html' })
export class PortfolioTotalComponent implements OnInit {
    portfolioData: PortfolioData;

    constructor(
        private portfolioDataService: PortfolioDataService
    ) { }

    ngOnInit() {
        this.portfolioDataService.getPortfolioData()
            .pipe(first())
            .subscribe(portfolioData => this.portfolioData = portfolioData);

    }
}
