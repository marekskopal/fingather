import { HttpClient } from '@angular/common/http';
import {inject, Injectable} from '@angular/core';
import { AssetData } from '@app/models';
import { RangeEnum } from '@app/models/enums/range-enum';
import { buildHttpParams } from '@app/utils/http-params-builder';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class AssetDataService {
    private readonly http = inject(HttpClient);

    public async getAssetDataRange(assetId: number, range: RangeEnum): Promise<AssetData[]> {
        return firstValueFrom<AssetData[]>(
            this.http.get<AssetData[]>(
                `${environment.apiUrl}/asset-data-range/${assetId}`,
                { params: buildHttpParams({ range }) },
            ),
        );
    }
}
