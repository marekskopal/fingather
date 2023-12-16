import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable } from 'rxjs';
import { map } from 'rxjs/operators';

import { environment } from '@environments/environment';
import { tickerData } from '@app/_models';

@Injectable({ providedIn: 'root' })
export class AssetTickerDataService {
    constructor(
        private router: Router,
        private http: HttpClient
    ) {}

    findLastYear(assetTickerId: string) {
        return this.http.get<tickerData[]>(`${environment.apiUrl}/assettickerdata/${assetTickerId}`);
    }
}
