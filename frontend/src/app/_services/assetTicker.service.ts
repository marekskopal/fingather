import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import {HttpClient, HttpParams} from '@angular/common/http';
import { BehaviorSubject, Observable } from 'rxjs';
import { map } from 'rxjs/operators';

import { environment } from '@environments/environment';
import { AssetTicker } from '@app/_models';

@Injectable({ providedIn: 'root' })
export class AssetTickerService {
    constructor(
        private router: Router,
        private http: HttpClient
    ) {}

    getByTicker(ticker: string) {
        let params = new HttpParams().set('ticker', ticker);

        return this.http.get<AssetTicker>(`${environment.apiUrl}/assetticker`, {params});
    }
}
