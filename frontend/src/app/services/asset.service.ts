import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { map } from 'rxjs/operators';

import { environment } from '@environments/environment';
import {Asset, AssetWithProperties} from '@app/models';
import {Observable} from "rxjs";
import {OkResponse} from "@app/models/ok-response";

@Injectable({ providedIn: 'root' })
export class AssetService {
    public constructor(
        private http: HttpClient
    ) {}

    public createAsset(asset: AssetWithProperties): Observable<Asset> {
        return this.http.post<Asset>(`${environment.apiUrl}/asset`, asset);
    }

    public getAssets(): Observable<Asset[]> {
        return this.http.get<Asset[]>(`${environment.apiUrl}/asset`);
    }

    public getOpenedAssets(): Observable<AssetWithProperties[]> {
        return this.http.get<AssetWithProperties[]>(`${environment.apiUrl}/asset/opened`);
    }

    public getClosedAssets(): Observable<Asset[]> {
        return this.http.get<Asset[]>(`${environment.apiUrl}/asset/closed`);
    }

    public getWatchedAssets(): Observable<Asset[]> {
        return this.http.get<Asset[]>(`${environment.apiUrl}/asset/watched`);
    }

    public getAsset(id: number): Observable<AssetWithProperties> {
        return this.http.get<AssetWithProperties>(`${environment.apiUrl}/asset/${id}`);
    }

    public updateAsset(id: number, asset: AssetWithProperties): Observable<AssetWithProperties> {
        return this.http.put<AssetWithProperties>(`${environment.apiUrl}/asset/${id}`, asset)
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
