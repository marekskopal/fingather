import { EventEmitter } from '@angular/core';

export abstract class NotifyService {
    private readonly eventEmitter: EventEmitter<null> = new EventEmitter();

    public subscribe(callback: () => void): void {
        this.eventEmitter.subscribe(callback);
    }

    public unsubscribe(): void {
        this.eventEmitter.unsubscribe();
    }

    public notify(): void {
        this.eventEmitter.emit();
    }
}
