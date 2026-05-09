import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { TaxOptimization } from '@app/models/tax-optimization';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class TaxOptimizationService {
    private readonly http = inject(HttpClient);

    public getTaxOptimization(portfolioId: number): Promise<TaxOptimization> {
        return firstValueFrom<TaxOptimization>(
            this.http.get<TaxOptimization>(`${environment.apiUrl}/tax-optimization/${portfolioId}`),
        );
    }
}
