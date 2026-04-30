import {
    ChangeDetectionStrategy,
    Component,
    computed,
    DestroyRef,
    inject,
    OnDestroy,
    OnInit,
    signal,
} from '@angular/core';
import { MatIcon } from '@angular/material/icon';
import { AuthenticationService } from '@app/services/authentication.service';
import { TranslatePipe } from '@ngx-translate/core';

const AUTO_STOP_THRESHOLD_SECONDS = 30;

@Component({
    selector: 'fingather-impersonation-banner',
    templateUrl: 'impersonation-banner.component.html',
    styleUrls: ['impersonation-banner.component.scss'],
    standalone: true,
    changeDetection: ChangeDetectionStrategy.OnPush,
    imports: [MatIcon, TranslatePipe],
})
export class ImpersonationBannerComponent implements OnInit, OnDestroy {
    private readonly authenticationService = inject(AuthenticationService);
    private readonly destroyRef = inject(DestroyRef);

    protected readonly impersonation = this.authenticationService.impersonation;
    protected readonly isImpersonating = this.authenticationService.isImpersonating;

    private readonly nowSeconds = signal<number>(Math.floor(Date.now() / 1000));
    private intervalHandle: ReturnType<typeof setInterval> | null = null;

    protected readonly remainingSeconds = computed<number>(() => {
        const state = this.impersonation();
        if (state === null) {
            return 0;
        }
        return Math.max(0, state.expiresAt - this.nowSeconds());
    });

    protected readonly remainingDisplay = computed<string>(() => {
        const total = this.remainingSeconds();
        const minutes = Math.floor(total / 60);
        const seconds = total % 60;
        return `${minutes}:${seconds.toString().padStart(2, '0')}`;
    });

    public ngOnInit(): void {
        this.intervalHandle = setInterval(() => {
            this.nowSeconds.set(Math.floor(Date.now() / 1000));

            if (!this.isImpersonating()) {
                return;
            }
            if (this.remainingSeconds() <= AUTO_STOP_THRESHOLD_SECONDS) {
                this.handleAutoStop();
            }
        }, 1000);

        this.destroyRef.onDestroy(() => this.clearInterval());
    }

    public ngOnDestroy(): void {
        this.clearInterval();
    }

    protected async stopImpersonation(): Promise<void> {
        await this.authenticationService.stopImpersonation();
    }

    private handleAutoStop(): void {
        this.clearInterval();
        void this.authenticationService.stopImpersonation();
    }

    private clearInterval(): void {
        if (this.intervalHandle !== null) {
            clearInterval(this.intervalHandle);
            this.intervalHandle = null;
        }
    }
}
