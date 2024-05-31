import { HttpClient, HttpParams } from '@angular/common/http';
import {inject, Injectable} from '@angular/core';
import { Asset, AssetsWithProperties, AssetWithProperties } from '@app/models';
import { AssetCreate } from '@app/models/asset-create';
import { AssetsOrder } from '@app/models/enums/assets-order';
import { NotifyService } from '@app/services/notify-service';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class AssetService extends NotifyService {
    private http = inject(HttpClient);

    public createAsset(asset: AssetCreate, portfolioId: number): Promise<Asset> {
        return firstValueFrom<Asset>(this.http.post<Asset>(`${environment.apiUrl}/assets/${portfolioId}`, asset));
    }

    public getAssets(portfolioId: number): Promise<Asset[]> {
        return firstValueFrom<Asset[]>(this.http.get<Asset[]>(`${environment.apiUrl}/assets/${portfolioId}`));
    }

    public getAssetsWithProperties(portfolioId: number, orderBy: AssetsOrder): Promise<AssetsWithProperties> {
        let params = new HttpParams();

        params = params.set('orderBy', orderBy.toString());

        return firstValueFrom<AssetsWithProperties>(this.http.get<AssetsWithProperties>(
            `${environment.apiUrl}/assets/with-properties/${portfolioId}`,
            { params },
        ));
    }

    public getAsset(id: number): Promise<AssetWithProperties> {
        return firstValueFrom<AssetWithProperties>(
            this.http.get<AssetWithProperties>(`${environment.apiUrl}/asset/${id}`)
        );
    }
}
