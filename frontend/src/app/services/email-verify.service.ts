import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { OkResponse } from '@app/models/ok-response';
import { NotifyService } from '@app/services/notify-service';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class EmailVerifyService extends NotifyService {
    public constructor(
        private http: HttpClient,
    ) {
        super();
    }

    public verifyEmail(token: string): Promise<OkResponse> {
        return firstValueFrom<OkResponse>(
            this.http.post<OkResponse>(`${environment.apiUrl}/email-verify`, {
                token,
            })
        );
    }
}
