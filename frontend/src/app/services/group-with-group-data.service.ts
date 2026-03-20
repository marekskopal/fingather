import { HttpClient } from '@angular/common/http';
import {inject, Injectable} from '@angular/core';
import { GroupWithGroupData } from '@app/models';
import { AssetsOrder } from '@app/models/enums/assets-order';
import {GroupAllocationService} from "@app/services/group-allocation-service";
import { buildHttpParams } from '@app/utils/http-params-builder';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class GroupWithGroupDataService implements GroupAllocationService {
    private readonly http = inject(HttpClient);

    public async getGroupAllocations(portfolioId: number): Promise<GroupWithGroupData[]> {
        return this.getGroupsWithGroupData(portfolioId);
    }

    public getGroupsWithGroupData(
        portfolioId: number,
        orderBy: AssetsOrder | null = null,
    ): Promise<GroupWithGroupData[]> {
        return firstValueFrom<GroupWithGroupData[]>(
            this.http.get<GroupWithGroupData[]>(
                `${environment.apiUrl}/groups-with-group-data/${portfolioId}`,
                { params: buildHttpParams({ orderBy }) },
            ),
        );
    }
}
