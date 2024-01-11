﻿import {Injectable} from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { NotifyService } from '.';
import {Observable} from "rxjs";
import {OkResponse} from "@app/models/ok-response";

@Injectable({ providedIn: 'root' })
export class EmailVerifyService extends NotifyService {
    public constructor(
        private http: HttpClient,
    ) {
        super();
    }

    public verifyEmail(token: string): Observable<OkResponse> {
        return this.http.post<OkResponse>(`${environment.apiUrl}/email-verify`, {
            token: token,
        });
    }
}
