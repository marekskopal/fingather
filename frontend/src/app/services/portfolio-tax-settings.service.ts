import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { PortfolioTaxSettings, PortfolioTaxSettingsUpdate } from '@app/models/portfolio-tax-settings';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class PortfolioTaxSettingsService {
    private readonly http = inject(HttpClient);

    public getTaxSettings(portfolioId: number): Promise<PortfolioTaxSettings> {
        return firstValueFrom<PortfolioTaxSettings>(
            this.http.get<PortfolioTaxSettings>(`${environment.apiUrl}/portfolio/${portfolioId}/tax-settings`),
        );
    }

    public updateTaxSettings(
        portfolioId: number,
        settings: PortfolioTaxSettingsUpdate,
    ): Promise<PortfolioTaxSettings> {
        return firstValueFrom<PortfolioTaxSettings>(
            this.http.put<PortfolioTaxSettings>(`${environment.apiUrl}/portfolio/${portfolioId}/tax-settings`, settings),
        );
    }
}
