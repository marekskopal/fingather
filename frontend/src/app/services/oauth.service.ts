import {HttpClient} from '@angular/common/http';
import {inject, Injectable} from '@angular/core';
import {environment} from '@environments/environment';
import {firstValueFrom} from 'rxjs';

export interface OAuthClientInfo {
    clientName: string;
}

export interface OAuthAuthorizeRequest {
    clientId: string;
    redirectUri: string;
    codeChallenge: string;
    codeChallengeMethod: string;
    state: string;
}

export interface OAuthAuthorizeResponse {
    code: string;
    redirectUri: string;
    state: string;
}

@Injectable({providedIn: 'root'})
export class OAuthService {
    private readonly http = inject(HttpClient);

    public async getClientInfo(clientId: string): Promise<OAuthClientInfo> {
        return firstValueFrom(
            this.http.get<OAuthClientInfo>(`${environment.apiUrl}/mcp/oauth/client-info`, {
                params: {client_id: clientId},
            }),
        );
    }

    public async authorize(request: OAuthAuthorizeRequest): Promise<OAuthAuthorizeResponse> {
        return firstValueFrom(
            this.http.post<OAuthAuthorizeResponse>(`${environment.apiUrl}/mcp/oauth/authorize`, request),
        );
    }
}
