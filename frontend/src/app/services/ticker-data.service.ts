import { HttpClient } from '@angular/common/http';
import {inject, Injectable} from '@angular/core';
import { TickerData } from '@app/models';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class TickerDataService {
    private readonly http = inject(HttpClient);

    public getTickerDatas(assetTickerId: number): Promise<TickerData[]> {
        return firstValueFrom<TickerData[]>(
            this.http.get<TickerData[]>(`${environment.apiUrl}/ticker-data/${assetTickerId}`)
        );
    }
}
