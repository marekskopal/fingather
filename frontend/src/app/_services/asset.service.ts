import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable } from 'rxjs';
import { map } from 'rxjs/operators';

import { environment } from '@environments/environment';
import { Asset } from '@app/_models';

@Injectable({ providedIn: 'root' })
export class AssetService {
    constructor(
        private router: Router,
        private http: HttpClient
    ) {}

    create(asset: Asset) {
        return this.http.post(`${environment.apiUrl}/asset`, asset);
    }

    findAll() {
        return this.http.get<Asset[]>(`${environment.apiUrl}/asset`);
    }

    getById(id: number) {
        return this.http.get<Asset>(`${environment.apiUrl}/asset/${id}`);
    }

    update(id: number, params) {
        return this.http.put(`${environment.apiUrl}/asset/${id}`, params)
            .pipe(map(x => {
                return x;
            }));
    }

    delete(id: number) {
        return this.http.delete(`${environment.apiUrl}/asset/${id}`)
            .pipe(map(x => {
                return x;
            }));
    }
}
