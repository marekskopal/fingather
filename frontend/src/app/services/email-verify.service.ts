import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { OkResponse } from '@app/models/ok-response';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class EmailVerifyService {
    public constructor(
        private readonly http: HttpClient,
    ) {
    }

    public verifyEmail(token: string): Promise<OkResponse> {
        return firstValueFrom<OkResponse>(
            this.http.post<OkResponse>(`${environment.apiUrl}/email-verify`, {
                token,
            })
        );
    }
}
