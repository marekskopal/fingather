import { HttpClient } from '@angular/common/http';
import {Injectable} from '@angular/core';
import {OkResponse} from '@app/models/ok-response';
import { environment } from '@environments/environment';
import {Observable} from 'rxjs';

import { NotifyService } from '.';

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
