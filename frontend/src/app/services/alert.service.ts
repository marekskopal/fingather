import { Injectable } from '@angular/core';
import { Observable, Subject } from 'rxjs';
import { filter } from 'rxjs/operators';

import { Alert, AlertType } from '@app/models';

@Injectable({ providedIn: 'root' })
export class AlertService {
    private subject = new Subject<Alert>();
    private defaultId = 'default-alert';

    // enable subscribing to alerts observable
    public onAlert(id = this.defaultId): Observable<Alert> {
        return this.subject.asObservable().pipe(filter(x => x && x.id === id));
    }

    // convenience methods
    public success(message: string, options?: Partial<Alert>): void {
        this.alert(new Alert({ ...options, type: AlertType.Success, message }));
    }

    public error(message: string, options?: Partial<Alert>): void {
        this.alert(new Alert({ ...options, type: AlertType.Error, message }));
    }

    public info(message: string, options?: Partial<Alert>): void {
        this.alert(new Alert({ ...options, type: AlertType.Info, message }));
    }

    public warning(message: string, options?: Partial<Alert>): void {
        this.alert(new Alert({ ...options, type: AlertType.Warning, message }));
    }

    // main alert method
    private alert(alert: Alert): void {
        alert.id = alert.id || this.defaultId;
        this.subject.next(alert);
    }

    // clear alerts
    public clear(id = this.defaultId): void {
        this.subject.next(new Alert({ id }));
    }
}
