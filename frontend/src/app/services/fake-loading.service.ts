import { Injectable, signal} from '@angular/core';

@Injectable({
    providedIn: 'root'
})
export class FakeLoadingService {
    public readonly $processed = signal<number>(0);

    private loadingIntervalId: number | undefined = undefined;
    private loadingStartTime: number = 0;

    public startLoading(): void {
        this.loadingStartTime = Date.now();

        this.loadingIntervalId = setInterval(() => {
            const elapsedTime = Date.now() - this.loadingStartTime;
            const nextProcessed =  Math.round(Math.atan(elapsedTime / 3e3) / (Math.PI / 2) * 100);

            this.$processed.set(nextProcessed);
        }, 100);
    }

    public finishLoading(): void {
        clearInterval(this.loadingIntervalId);
        this.$processed.set(100);
        this.loadingStartTime = 0;
    }
}
