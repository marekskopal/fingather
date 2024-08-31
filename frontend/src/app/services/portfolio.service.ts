import { HttpClient } from '@angular/common/http';
import {inject, Injectable} from '@angular/core';
import { Portfolio } from '@app/models';
import { OkResponse } from '@app/models/ok-response';
import { NotifyService } from '@app/services/notify-service';
import { environment } from '@environments/environment';
import { firstValueFrom, lastValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class PortfolioService extends NotifyService {
    private readonly http = inject(HttpClient);

    private currentPortfolio: Portfolio | null = null;
    private defaultPortfolio: Portfolio | null = null;

    public createPortfolio(portfolio: Portfolio): Promise<Portfolio> {
        return firstValueFrom<Portfolio>(this.http.post<Portfolio>(`${environment.apiUrl}/portfolios`, portfolio));
    }

    public getPortfolios(): Promise<Portfolio[]> {
        return firstValueFrom<Portfolio[]>(this.http.get<Portfolio[]>(`${environment.apiUrl}/portfolios`));
    }

    public getPortfolio(id: number): Promise<Portfolio> {
        return firstValueFrom<Portfolio>(this.http.get<Portfolio>(`${environment.apiUrl}/portfolio/${id}`));
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

    public updatePortfolio(id: number, portfolio: Portfolio): Promise<Portfolio> {
        return firstValueFrom<Portfolio>(
            this.http.put<Portfolio>(`${environment.apiUrl}/portfolio/${id}`, portfolio)
        );
    }

    public deletePortfolio(id: number): Promise<OkResponse> {
        return firstValueFrom<OkResponse>(this.http.delete<OkResponse>(`${environment.apiUrl}/portfolio/${id}`));
    }
}
