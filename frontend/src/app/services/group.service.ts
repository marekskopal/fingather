import {Injectable} from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { map } from 'rxjs/operators';

import { environment } from '@environments/environment';
import { Group } from '@app/models';
import { ANotifyService } from '.';

@Injectable({ providedIn: 'root' })
export class GroupService extends ANotifyService {
    constructor(
        private http: HttpClient,
    ) {
        super();
    }

    public create(group: Group) {
        return this.http.post(`${environment.apiUrl}/group`, group);
    }

    public findAll() {
        return this.http.get<Group[]>(`${environment.apiUrl}/group`);
    }

    public getById(id: number) {
        return this.http.get<Group>(`${environment.apiUrl}/group/${id}`);
    }

    public getOthersGroup() {
        return this.http.get<Group>(`${environment.apiUrl}/group/others`);
    }

    public update(id: number, group: Group) {
        return this.http.put(`${environment.apiUrl}/group/${id}`, group)
            .pipe(map(x => {
                return x;
            }));
    }

    public delete(id: number) {
        return this.http.delete(`${environment.apiUrl}/group/${id}`)
            .pipe(map(x => {
                return x;
            }));
    }
}
