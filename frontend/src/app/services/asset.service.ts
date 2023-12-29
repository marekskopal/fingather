﻿import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { map } from 'rxjs/operators';

import { environment } from '@environments/environment';
import { Asset } from '@app/models';

@Injectable({ providedIn: 'root' })
export class AssetService {
    constructor(
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

    update(id: number, params: any) {
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
