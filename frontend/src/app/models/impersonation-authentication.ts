export interface ImpersonationAuthentication {
    accessToken: string;
    expiresAt: number;
    sessionId: number;
    targetUserId: number;
    targetUserEmail: string;
    targetUserName: string;
}
