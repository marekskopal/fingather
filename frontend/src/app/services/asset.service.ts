import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '@environments/environment';
import {Asset, AssetWithProperties} from '@app/models';
import {Observable} from "rxjs";
import {AssetCreate} from "@app/models/asset-create";
import {NotifyService} from "@app/services/notify-service";

@Injectable({ providedIn: 'root' })
export class AssetService extends NotifyService {
    public constructor(
        private http: HttpClient
    ) {
        super();
    }

    public createAsset(asset: AssetCreate): Observable<Asset> {
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
}
