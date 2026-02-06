import {HttpClient} from '@angular/common/http';
import {inject, Injectable} from '@angular/core';
import {BenchmarkAsset} from '@app/models';
import {environment} from '@environments/environment';
import {firstValueFrom} from 'rxjs';

@Injectable({providedIn: 'root'})
export class BenchmarkAssetService {
    private readonly http = inject(HttpClient);

    public getBenchmarkAssets(): Promise<BenchmarkAsset[]> {
        return firstValueFrom(this.http.get<BenchmarkAsset[]>(`${environment.apiUrl}/benchmark-assets`));
    }

    public getAdminBenchmarkAssets(): Promise<BenchmarkAsset[]> {
        return firstValueFrom(this.http.get<BenchmarkAsset[]>(`${environment.apiUrl}/admin/benchmark-assets`));
    }

    public createBenchmarkAsset(data: {tickerId: number}): Promise<BenchmarkAsset> {
        return firstValueFrom(this.http.post<BenchmarkAsset>(`${environment.apiUrl}/admin/benchmark-assets`, data));
    }

    public deleteBenchmarkAsset(id: number): Promise<void> {
        return firstValueFrom(this.http.delete<void>(`${environment.apiUrl}/admin/benchmark-asset/${id}`));
    }
}
