import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { Currency } from '@app/models';
import {CurrentUserService} from "@app/services/current-user.service";
import {Observable} from "rxjs";
import {Portfolio} from "@app/models/portfolio";

@Injectable({ providedIn: 'root' })
export class PortfolioService {
    private defaultPortfolio: Portfolio|null = null;

    public constructor(
        private http: HttpClient,
    ) {}

    public getPortfolios(): Observable<Portfolio[]> {
        return this.http.get<Portfolio[]>(`${environment.apiUrl}/portfolio`);
    }

    public async getDefaultPortfolio(): Promise<Portfolio> {
        if (this.defaultPortfolio !== null) {
            return this.defaultPortfolio;
        }

        this.defaultPortfolio = await this.http.get<Portfolio>(`${environment.apiUrl}/portfolio/default`).toPromise();

        return this.defaultPortfolio;
    }
}
