import {EventEmitter, Injectable} from '@angular/core';
import { Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { map } from 'rxjs/operators';

import { environment } from '@environments/environment';
import { Group } from '@app/_models';
import { ANotifyService } from '.';

@Injectable({ providedIn: 'root' })
export class GroupService extends ANotifyService {
    public eventEmitter: EventEmitter<null> = new EventEmitter();

    constructor(
        private router: Router,
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

    public getById(id: string) {
        return this.http.get<Group>(`${environment.apiUrl}/group/${id}`);
    }

    public getOthersGroup() {
        return this.http.get<Group>(`${environment.apiUrl}/group/others`);
    }

    public update(id, params) {
        return this.http.put(`${environment.apiUrl}/group/${id}`, params)
            .pipe(map(x => {
                return x;
            }));
    }

    public delete(id: string) {
        return this.http.delete(`${environment.apiUrl}/group/${id}`)
            .pipe(map(x => {
                return x;
            }));
    }
}
