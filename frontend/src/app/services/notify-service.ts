import { Observable, Subject } from 'rxjs';

export abstract class NotifyService {
    private readonly notifySubject: Subject<null> = new Subject<null>();
    private readonly notify$: Observable<null> = this.notifySubject.asObservable();

    public subscribe(callback: () => void): void {
        this.notify$.subscribe(callback);
    }

    public notify(): void {
        this.notifySubject.next(null);
    }
}
