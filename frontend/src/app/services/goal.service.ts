import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { Goal } from '@app/models';
import { OkResponse } from '@app/models/ok-response';
import { NotifyService } from '@app/services/notify-service';
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class GoalService extends NotifyService {
    private readonly http = inject(HttpClient);

    public getGoals(portfolioId: number): Promise<Goal[]> {
        return firstValueFrom<Goal[]>(this.http.get<Goal[]>(`${environment.apiUrl}/goals/${portfolioId}`));
    }

    public getGoal(id: number): Promise<Goal> {
        return firstValueFrom<Goal>(this.http.get<Goal>(`${environment.apiUrl}/goal/${id}`));
    }

    public createGoal(goal: Partial<Goal>): Promise<Goal> {
        return firstValueFrom<Goal>(this.http.post<Goal>(`${environment.apiUrl}/goals`, goal));
    }

    public updateGoal(id: number, goal: Partial<Goal>): Promise<Goal> {
        return firstValueFrom<Goal>(this.http.put<Goal>(`${environment.apiUrl}/goal/${id}`, goal));
    }

    public deleteGoal(id: number): Promise<OkResponse> {
        return firstValueFrom<OkResponse>(this.http.delete<OkResponse>(`${environment.apiUrl}/goal/${id}`));
    }
}
