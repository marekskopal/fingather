﻿import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '@environments/environment';
import {Portfolio} from '@app/models';
import {Observable} from "rxjs";
import {NotifyService} from "@app/services/notify-service";
import {OkResponse} from "@app/models/ok-response";

@Injectable({ providedIn: 'root' })
export class PortfolioService extends NotifyService {
    private currentPortfolio: Portfolio|null = null;
    private defaultPortfolio: Portfolio|null = null;

    public constructor(
        private http: HttpClient,
    ) {
        super();
    }

    public createPortfolio(portfolio: Portfolio): Observable<Portfolio> {
        return this.http.post<Portfolio>(`${environment.apiUrl}/portfolios`, portfolio);
    }

    public getPortfolios(): Observable<Portfolio[]> {
        return this.http.get<Portfolio[]>(`${environment.apiUrl}/portfolios`);
    }

    public getPortfolio(id: number): Observable<Portfolio> {
        return this.http.get<Portfolio>(`${environment.apiUrl}/portfolio/${id}`);
    }

    public async getCurrentPortfolio(): Promise<Portfolio> {
        if (this.currentPortfolio !== null) {
            return this.currentPortfolio;
        }

        const localStorageCurrentPortfolio = localStorage.getItem('currentPortfolio');
        if (localStorageCurrentPortfolio !== null) {
            this.currentPortfolio = JSON.parse(localStorageCurrentPortfolio);
            if (this.currentPortfolio !== null) {
                return this.currentPortfolio;
            }
        }

        this.currentPortfolio = await this.getDefaultPortfolio();
        localStorage.setItem('currentPortfolio', JSON.stringify(this.currentPortfolio));

        return this.currentPortfolio;
    }

    public setCurrentPortfolio(currentPortfolio: Portfolio): void {
        localStorage.setItem('currentPortfolio', JSON.stringify(currentPortfolio));
        this.currentPortfolio = currentPortfolio;
    }

    public cleanCurrentPortfolio(): void {
        localStorage.removeItem('currentPortfolio');
        this.currentPortfolio = null;
    }

    public async getDefaultPortfolio(): Promise<Portfolio> {
        if (this.defaultPortfolio !== null) {
            return this.defaultPortfolio;
        }

        this.defaultPortfolio = await this.http.get<Portfolio>(`${environment.apiUrl}/portfolio/default`).toPromise();

        return this.defaultPortfolio;
    }

    public updatePortfolio(id: number, portfolio: Portfolio): Observable<Portfolio> {
        return this.http.put<Portfolio>(`${environment.apiUrl}/portfolio/${id}`, portfolio);
    }

    public deletePortfolio(id: number): Observable<OkResponse> {
        return this.http.delete<OkResponse>(`${environment.apiUrl}/portfolio/${id}`);
    }
}