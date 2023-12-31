import {Injectable} from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { map } from 'rxjs/operators';

import { environment } from '@environments/environment';
import { Group } from '@app/models';
import { NotifyService } from '.';
import {Observable} from "rxjs";
import {OkResponse} from "@app/models/ok-response";

@Injectable({ providedIn: 'root' })
export class GroupService extends NotifyService {
    public constructor(
        private http: HttpClient,
    ) {
        super();
    }

    public create(group: Group): Observable<Group> {
        return this.http.post<Group>(`${environment.apiUrl}/group`, group);
    }

    public findAll(): Observable<Group[]> {
        return this.http.get<Group[]>(`${environment.apiUrl}/group`);
    }

    public getById(id: number): Observable<Group> {
        return this.http.get<Group>(`${environment.apiUrl}/group/${id}`);
    }

    public getOthersGroup(): Observable<Group> {
        return this.http.get<Group>(`${environment.apiUrl}/group/others`);
    }

    public update(id: number, group: Group): Observable<Group> {
        return this.http.put<Group>(`${environment.apiUrl}/group/${id}`, group)
            .pipe(map(x => {
                return x;
            }));
    }

    public delete(id: number): Observable<OkResponse> {
        return this.http.delete<OkResponse>(`${environment.apiUrl}/group/${id}`)
            .pipe(map(x => {
                return x;
            }));
    }
}
