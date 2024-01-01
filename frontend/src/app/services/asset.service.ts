import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { map } from 'rxjs/operators';

import { environment } from '@environments/environment';
import { Asset } from '@app/models';
import {Observable} from "rxjs";
import {OkResponse} from "@app/models/ok-response";

@Injectable({ providedIn: 'root' })
export class AssetService {
    public constructor(
        private http: HttpClient
    ) {}

    public createAsset(asset: Asset): Observable<Asset> {
        return this.http.post<Asset>(`${environment.apiUrl}/asset`, asset);
    }

    public getAssets(): Observable<Asset[]> {
        return this.http.get<Asset[]>(`${environment.apiUrl}/asset`);
    }

    public getAsset(id: number): Observable<Asset> {
        return this.http.get<Asset>(`${environment.apiUrl}/asset/${id}`);
    }

    public updateAsset(id: number, asset: Asset): Observable<Asset> {
        return this.http.put<Asset>(`${environment.apiUrl}/asset/${id}`, asset)
            .pipe(map(x => {
                return x;
            }));
    }

    public deleteAsset(id: number): Observable<OkResponse> {
        return this.http.delete<OkResponse>(`${environment.apiUrl}/asset/${id}`)
            .pipe(map(x => {
                return x;
            }));
    }
}
