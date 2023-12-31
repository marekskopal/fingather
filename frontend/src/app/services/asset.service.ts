import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { map } from 'rxjs/operators';

import { environment } from '@environments/environment';
import { Asset } from '@app/models';

@Injectable({ providedIn: 'root' })
export class AssetService {
    public constructor(
        private http: HttpClient
    ) {}

    public create(asset: Asset) {
        return this.http.post(`${environment.apiUrl}/asset`, asset);
    }

    public findAll() {
        return this.http.get<Asset[]>(`${environment.apiUrl}/asset`);
    }

    public getById(id: number) {
        return this.http.get<Asset>(`${environment.apiUrl}/asset/${id}`);
    }

    public update(id: number, asset: Asset) {
        return this.http.put(`${environment.apiUrl}/asset/${id}`, asset)
            .pipe(map(x => {
                return x;
            }));
    }

    public delete(id: number) {
        return this.http.delete(`${environment.apiUrl}/asset/${id}`)
            .pipe(map(x => {
                return x;
            }));
    }
}
