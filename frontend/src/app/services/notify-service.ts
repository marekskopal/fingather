import { DestroyRef } from '@angular/core';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { Observable, Subject } from 'rxjs';

export abstract class NotifyService {
    private readonly notifySubject: Subject<null> = new Subject<null>();
    private readonly notify$: Observable<null> = this.notifySubject.asObservable();

    public subscribe(callback: () => void, destroyRef: DestroyRef): void {
        this.notify$.pipe(takeUntilDestroyed(destroyRef)).subscribe(callback);
    }

    public notify(): void {
        this.notifySubject.next(null);
    }
}
