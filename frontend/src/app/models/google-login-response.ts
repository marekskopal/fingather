import { Authentication } from './authentication';

export interface GoogleLoginRequiresCurrency {
    requiresCurrency: true;
    email: string;
    name: string;
}

export type GoogleLoginResponse = Authentication | GoogleLoginRequiresCurrency;

export function isGoogleLoginRequiresCurrency(response: GoogleLoginResponse): response is GoogleLoginRequiresCurrency {
    return 'requiresCurrency' in response && response.requiresCurrency === true;
}
