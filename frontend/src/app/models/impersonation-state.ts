export interface ImpersonationState {
    sessionId: number;
    targetUserId: number;
    targetUserEmail: string;
    targetUserName: string;
    expiresAt: number;
}
