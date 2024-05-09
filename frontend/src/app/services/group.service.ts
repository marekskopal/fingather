import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Group } from '@app/models';
import { OkResponse } from '@app/models/ok-response';
import { NotifyService } from '@app/services/notify-service';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class GroupService extends NotifyService {
    public constructor(
        private http: HttpClient,
    ) {
        super();
    }

    public createGroup(group: Group, portfolioId: number): Promise<Group> {
        return firstValueFrom<Group>(this.http.post<Group>(`${environment.apiUrl}/groups/${portfolioId}`, group));
    }

    public getGroups(portfolioId: number): Promise<Group[]> {
        return firstValueFrom<Group[]>(this.http.get<Group[]>(`${environment.apiUrl}/groups/${portfolioId}`));
    }

    public getGroup(id: number): Promise<Group> {
        return firstValueFrom<Group>(this.http.get<Group>(`${environment.apiUrl}/group/${id}`));
    }

    public getOthersGroup(portfolioId: number): Promise<Group> {
        return firstValueFrom<Group>(this.http.get<Group>(`${environment.apiUrl}/group/others/${portfolioId}`));
    }

    public updateGroup(id: number, group: Group): Promise<Group> {
        return firstValueFrom<Group>(this.http.put<Group>(`${environment.apiUrl}/group/${id}`, group));
    }

    public deleteGroup(id: number): Promise<OkResponse> {
        return firstValueFrom<OkResponse>(this.http.delete<OkResponse>(`${environment.apiUrl}/group/${id}`));
    }
}
