import { Injectable } from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import { map } from 'rxjs/operators';

import { environment } from '@environments/environment';
import { Dividend } from '@app/models';
import { NotifyService } from '.';
import {Observable} from "rxjs";
import {OkResponse} from "@app/models/ok-response";

@Injectable({ providedIn: 'root' })
export class DividendService extends NotifyService {
    public constructor(
        private http: HttpClient
    ) {
        super();
    }

    public create(dividend: Dividend): Observable<Dividend> {
        return this.http.post<Dividend>(`${environment.apiUrl}/dividend`, dividend);
    }

    public findAll(): Observable<Dividend[]> {
        return this.http.get<Dividend[]>(`${environment.apiUrl}/dividend`);
    }

    public findByAssetId(assetId: number): Observable<Dividend[]> {
        const params = new HttpParams().set('assetId', assetId);

        return this.http.get<Dividend[]>(`${environment.apiUrl}/dividend`, {params});
    }

    public getById(id: number): Observable<Dividend> {
        return this.http.get<Dividend>(`${environment.apiUrl}/dividend/${id}`)
            .pipe(map(dividend => {
                dividend.paidDate = new Date(String(dividend.paidDate));
                    return dividend;
                }
            ))
    }

    public update(id: number, dividend: Dividend): Observable<Dividend> {
        return this.http.put<Dividend>(`${environment.apiUrl}/dividend/${id}`, dividend)
            .pipe(map(x => {
                return x;
            }));
    }

    public delete(id: number): Observable<OkResponse> {
        return this.http.delete<OkResponse>(`${environment.apiUrl}/dividend/${id}`)
            .pipe(map(x => {
                return x;
            }));
    }
}
