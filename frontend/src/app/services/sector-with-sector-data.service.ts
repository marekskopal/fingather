import { HttpClient } from '@angular/common/http';
import {inject, Injectable} from '@angular/core';
import {SectorWithSectorData} from "@app/models/sector-with-sector-data";
import {GroupAllocationService} from "@app/services/group-allocation-service";
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class SectorWithSectorDataService implements GroupAllocationService {
    private readonly http = inject(HttpClient);

    public async getGroupAllocations(portfolioId: number): Promise<SectorWithSectorData[]> {
        return this.getSectorsWithSectorData(portfolioId);
    }

    public getSectorsWithSectorData(
        portfolioId: number,
    ): Promise<SectorWithSectorData[]> {
        return firstValueFrom<SectorWithSectorData[]>(
            this.http.get<SectorWithSectorData[]>(
                `${environment.apiUrl}/sectors-with-sector-data/${portfolioId}`,
            ),
        );
    }
}
