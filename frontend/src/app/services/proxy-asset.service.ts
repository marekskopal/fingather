import {HttpClient} from '@angular/common/http';
import {inject, Injectable} from '@angular/core';
import {ProxyAsset} from '@app/models';
import {TickerTypeEnum} from '@app/models/enums/ticker-type-enum';
import {environment} from '@environments/environment';
import {firstValueFrom} from 'rxjs';

@Injectable({providedIn: 'root'})
export class ProxyAssetService {
    private readonly http = inject(HttpClient);

    public getAdminProxyAssets(): Promise<ProxyAsset[]> {
        return firstValueFrom(this.http.get<ProxyAsset[]>(`${environment.apiUrl}/admin/proxy-assets`));
    }

    public createProxyAsset(data: {tickerType: TickerTypeEnum; tickerId: number}): Promise<ProxyAsset> {
        return firstValueFrom(this.http.post<ProxyAsset>(`${environment.apiUrl}/admin/proxy-assets`, data));
    }

    public deleteProxyAsset(id: number): Promise<void> {
        return firstValueFrom(this.http.delete<void>(`${environment.apiUrl}/admin/proxy-asset/${id}`));
    }
}
