import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Group } from '@app/models';
import { OkResponse } from '@app/models/ok-response';
import { NotifyService } from '@app/services/notify-service';
import { environment } from '@environments/environment';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';

@Injectable({ providedIn: 'root' })
export class GroupService extends NotifyService {
    public constructor(
        private http: HttpClient,
    ) {
        super();
    }

    public createGroup(group: Group, portfolioId: number): Observable<Group> {
        return this.http.post<Group>(`${environment.apiUrl}/groups/${portfolioId}`, group);
    }

    public getGroups(portfolioId: number): Observable<Group[]> {
        return this.http.get<Group[]>(`${environment.apiUrl}/groups/${portfolioId}`);
    }

    public getGroup(id: number): Observable<Group> {
        return this.http.get<Group>(`${environment.apiUrl}/group/${id}`);
    }

    public getOthersGroup(portfolioId: number): Observable<Group> {
        return this.http.get<Group>(`${environment.apiUrl}/group/others/${portfolioId}`);
    }

    public updateGroup(id: number, group: Group): Observable<Group> {
        return this.http.put<Group>(`${environment.apiUrl}/group/${id}`, group)
            .pipe(map((x) => x));
    }

    public deleteGroup(id: number): Observable<OkResponse> {
        return this.http.delete<OkResponse>(`${environment.apiUrl}/group/${id}`)
            .pipe(map((x) => x));
    }
}
