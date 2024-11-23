import { HttpClient } from '@angular/common/http';
import {inject, Injectable} from '@angular/core';
import {IndustryWithIndustryData} from "@app/models/industry-with-industry-data";
import {GroupAllocationService} from "@app/services/group-allocation-service";
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class IndustryWithIndustryDataService implements GroupAllocationService {
    private readonly http = inject(HttpClient);

    public async getGroupAllocations(portfolioId: number): Promise<IndustryWithIndustryData[]> {
        return this.getIndustriesWithIndustryData(portfolioId);
    }

    public getIndustriesWithIndustryData(
        portfolioId: number,
    ): Promise<IndustryWithIndustryData[]> {
        return firstValueFrom<IndustryWithIndustryData[]>(
            this.http.get<IndustryWithIndustryData[]>(
                `${environment.apiUrl}/industries-with-industry-data/${portfolioId}`,
            ),
        );
    }
}
