import { HttpParams } from '@angular/common/http';

type HttpParamValue = string | number | boolean | null | undefined;

export function buildHttpParams(params: Record<string, HttpParamValue>): HttpParams {
    let httpParams = new HttpParams();
    for (const [key, value] of Object.entries(params)) {
        if (value !== null && value !== undefined) {
            httpParams = httpParams.set(key, value);
        }
    }
    return httpParams;
}
