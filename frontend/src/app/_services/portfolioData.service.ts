import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import {HttpClient, HttpParams} from '@angular/common/http';
import { environment } from '@environments/environment';
import { PortfolioData } from '@app/_models';

@Injectable({ providedIn: 'root' })
export class PortfolioDataService {
    constructor(
        private router: Router,
        private http: HttpClient
    ) {}

    getPortfolioData() {
        return this.http.get<PortfolioData>(`${environment.apiUrl}/portfolio-data`);
    }
}
