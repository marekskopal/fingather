import { HttpClient } from '@angular/common/http';
import {inject, Injectable} from '@angular/core';
import { TaxReport } from '@app/models';
import {DownloadUtils} from "@app/utils/download-utils";
import { environment } from '@environments/environment';
import { firstValueFrom } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class TaxReportService {
    private readonly http = inject(HttpClient);

    public getTaxReport(portfolioId: number, year: number): Promise<TaxReport> {
        return firstValueFrom<TaxReport>(
            this.http.get<TaxReport>(`${environment.apiUrl}/tax-report/${portfolioId}/${year}`),
        );
    }

    public async exportXlsx(portfolioId: number, year: number): Promise<void> {
        const blob = await firstValueFrom(
            this.http.get(`${environment.apiUrl}/tax-report/${portfolioId}/${year}/export-xlsx`, { responseType: 'blob' }),
        );
        DownloadUtils.downloadBlob(blob, `tax-report-${year}.xlsx`);
    }

    public async exportPdf(portfolioId: number, year: number): Promise<void> {
        const blob = await firstValueFrom(
            this.http.get(`${environment.apiUrl}/tax-report/${portfolioId}/${year}/export-pdf`, { responseType: 'blob' }),
        );
        DownloadUtils.downloadBlob(blob, `tax-report-${year}.pdf`);
    }
}
