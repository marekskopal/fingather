import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Asset, AssetWithProperties } from '@app/models';
import { AssetCreate } from '@app/models/asset-create';
import { NotifyService } from '@app/services/notify-service';
import { environment } from '@environments/environment';
import { Observable } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class AssetService extends NotifyService {
    public constructor(
        private http: HttpClient
    ) {
        super();
    }

    public createAsset(asset: AssetCreate, portfolioId: number): Observable<Asset> {
        return this.http.post<Asset>(`${environment.apiUrl}/assets/${portfolioId}`, asset);
    }

    public getAssets(portfolioId: number): Observable<Asset[]> {
        return this.http.get<Asset[]>(`${environment.apiUrl}/assets/${portfolioId}`);
    }

    public getOpenedAssets(portfolioId: number): Observable<AssetWithProperties[]> {
        return this.http.get<AssetWithProperties[]>(`${environment.apiUrl}/assets/opened/${portfolioId}`);
    }

    public getClosedAssets(portfolioId: number): Observable<Asset[]> {
        return this.http.get<Asset[]>(`${environment.apiUrl}/assets/closed/${portfolioId}`);
    }

    public getWatchedAssets(portfolioId: number): Observable<Asset[]> {
        return this.http.get<Asset[]>(`${environment.apiUrl}/assets/watched/${portfolioId}`);
    }

    public getAsset(id: number): Observable<AssetWithProperties> {
        return this.http.get<AssetWithProperties>(`${environment.apiUrl}/asset/${id}`);
    }
}
