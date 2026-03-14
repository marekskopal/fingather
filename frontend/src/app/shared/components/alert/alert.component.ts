import {
    ChangeDetectionStrategy,
    Component, inject, input, OnDestroy, OnInit, signal,
} from '@angular/core';
import { NavigationStart, Router } from '@angular/router';
import { Alert, AlertType } from '@app/models';
import { AlertService } from '@app/services';
import { Subscription } from 'rxjs';

@Component({
    selector: 'fingather-alert',
    templateUrl: 'alert.component.html',
    standalone: true,
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AlertComponent implements OnInit, OnDestroy {
    private router = inject(Router);
    private alertService = inject(AlertService);

    public id = input<string>('default-alert');
    public fade = input<boolean>(true);

    public readonly alerts = signal<Alert[]>([]);
    public alertSubscription: Subscription;
    public routeSubscription: Subscription;

    public ngOnInit(): void {
        // subscribe to new alert notifications
        this.alertSubscription = this.alertService.onAlert(this.id())
            .subscribe((alert) => {
                // clear alerts when an empty alert is received
                if (!alert.message) {
                    // filter out alerts without 'keepAfterRouteChange' flag
                    this.alerts.update((alerts) => {
                        const kept = alerts.filter((x) => x.keepAfterRouteChange);
                        kept.forEach((x) => x.keepAfterRouteChange = false);
                        return kept;
                    });
                    return;
                }

                // add alert to array
                this.alerts.update((alerts) => [...alerts, alert]);

                // auto close alert if required
                if (alert.autoClose) {
                    setTimeout(() => this.removeAlert(alert), 3500);
                }
            });

        // clear alerts on location change
        this.routeSubscription = this.router.events.subscribe((event) => {
            if (event instanceof NavigationStart) {
                this.alertService.clear(this.id());
            }
        });
    }

    public ngOnDestroy(): void {
        // unsubscribe to avoid memory leaks
        this.alertSubscription.unsubscribe();
        this.routeSubscription.unsubscribe();
    }

    public removeAlert(alert: Alert): void {
        // check if already removed to prevent error on auto close
        if (!this.alerts().includes(alert)) return;

        if (this.fade()) {
            // fade out alert
            alert.fade = true;
            this.alerts.update((alerts) => [...alerts]);

            // remove alert after faded out
            setTimeout(() => {
                this.alerts.update((alerts) => alerts.filter((x) => x !== alert));
            }, 250);
        } else {
            this.alerts.update((alerts) => alerts.filter((x) => x !== alert));
        }
    }

    public cssClass(alert: Alert): string {
        const classes = ['alert', 'alert-dismissible', 'mt-4', 'container'];

        const alertTypeClass = {
            [AlertType.Success]: 'alert alert-success',
            [AlertType.Error]: 'alert alert-danger',
            [AlertType.Info]: 'alert alert-info',
            [AlertType.Warning]: 'alert alert-warning',
        };

        classes.push(alertTypeClass[alert.type]);

        if (alert.fade) {
            classes.push('fade');
        }

        return classes.join(' ');
    }
}
