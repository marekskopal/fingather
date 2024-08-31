import { HttpClient } from '@angular/common/http';
import {inject, Injectable} from '@angular/core';
import { OkResponse } from '@app/models/ok-response';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class OnboardingService {
    private readonly http = inject(HttpClient);

    public onboardingComplete(): Promise<OkResponse> {
        return firstValueFrom<OkResponse>(
            this.http.post<OkResponse>(`${environment.apiUrl}/onboarding-complete`, {})
        );
    }
}
