import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { CostBasisComparison } from '@app/models/cost-basis-comparison';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class CostBasisComparisonService {
    private readonly http = inject(HttpClient);

    public getCostBasisComparison(portfolioId: number, year: number): Promise<CostBasisComparison> {
        return firstValueFrom<CostBasisComparison>(
            this.http.get<CostBasisComparison>(
                `${environment.apiUrl}/tax-report/${portfolioId}/${year}/cost-basis-comparison`,
            ),
        );
    }
}
