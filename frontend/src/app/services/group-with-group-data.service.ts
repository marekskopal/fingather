import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import {GroupWithGroupData} from '@app/models';
import { environment } from '@environments/environment';
import {Observable} from 'rxjs';

@Injectable({ providedIn: 'root' })
export class GroupWithGroupDataService {
    public constructor(
        private http: HttpClient
    ) {}

    public getGroupWithGroupData(portfolioId: number): Observable<GroupWithGroupData[]> {
        return this.http.get<GroupWithGroupData[]>(`${environment.apiUrl}/groups-with-group-data/${portfolioId}`);
    }
}
