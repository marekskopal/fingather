import { HttpClient } from '@angular/common/http';
import {inject, Injectable} from '@angular/core';
import {CountryWithCountryData} from "@app/models/country-with-country-data";
import {GroupAllocationService} from "@app/services/group-allocation-service";
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class CountryWithCountryDataService implements GroupAllocationService {
    private readonly http = inject(HttpClient);

    public async getGroupAllocations(portfolioId: number): Promise<CountryWithCountryData[]> {
        return this.getCountryWithCountryData(portfolioId);
    }

    public getCountryWithCountryData(
        portfolioId: number,
    ): Promise<CountryWithCountryData[]> {
        return firstValueFrom<CountryWithCountryData[]>(
            this.http.get<CountryWithCountryData[]>(
                `${environment.apiUrl}/countries-with-country-data/${portfolioId}`,
            ),
        );
    }
}
