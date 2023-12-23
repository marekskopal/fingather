import { Component, OnInit } from '@angular/core';


import {first} from "rxjs/operators";
import {PortfolioData} from "@app/models";
import {PortfolioDataService} from "@app/services";

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
