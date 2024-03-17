import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Portfolio } from '@app/models';
import { OkResponse } from '@app/models/ok-response';
import { NotifyService } from '@app/services/notify-service';
import { environment } from '@environments/environment';
import { lastValueFrom, Observable } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class PortfolioService extends NotifyService {
    private currentPortfolio: Portfolio | null = null;
    private defaultPortfolio: Portfolio | null = null;

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
        this.defaultPortfolio = null;
    }

    public async getDefaultPortfolio(): Promise<Portfolio> {
        if (this.defaultPortfolio !== null) {
            return this.defaultPortfolio;
        }

        this.defaultPortfolio = await lastValueFrom<Portfolio>(
            this.http.get<Portfolio>(`${environment.apiUrl}/portfolio/default`)
        );

        return this.defaultPortfolio;
    }

    public updatePortfolio(id: number, portfolio: Portfolio): Observable<Portfolio> {
        return this.http.put<Portfolio>(`${environment.apiUrl}/portfolio/${id}`, portfolio);
    }

    public deletePortfolio(id: number): Observable<OkResponse> {
        return this.http.delete<OkResponse>(`${environment.apiUrl}/portfolio/${id}`);
    }
}
