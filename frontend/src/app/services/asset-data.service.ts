import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { AssetData } from '@app/models';
import { RangeEnum } from '@app/models/enums/range-enum';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class AssetDataService {
    public constructor(
        private http: HttpClient
    ) {}

    public async getAssetDataRange(assetId: number, range: RangeEnum): Promise<AssetData[]> {
        let params = new HttpParams();
        params = params.set('range', range);

        return firstValueFrom<AssetData[]>(
            this.http.get<AssetData[]>(
                `${environment.apiUrl}/asset-data-range/${assetId}`,
                { params }
            )
        );
    }
}
