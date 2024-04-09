import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { GroupWithGroupData } from '@app/models';
import { AssetsOrder } from '@app/models/enums/assets-order';
import { environment } from '@environments/environment';
import { Observable } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class GroupWithGroupDataService {
    public constructor(
        private http: HttpClient
    ) {}

    public getGroupWithGroupData(
        portfolioId: number,
        orderBy: AssetsOrder | null = null
    ): Observable<GroupWithGroupData[]> {
        let params = new HttpParams();

        if (orderBy !== null) {
            params = params.set('orderBy', orderBy.toString());
        }

        return this.http.get<GroupWithGroupData[]>(
            `${environment.apiUrl}/groups-with-group-data/${portfolioId}`,
            { params }
        );
    }
}
