import { EventEmitter } from '@angular/core';

export abstract class NotifyService {
    public eventEmitter: EventEmitter<null> = new EventEmitter();

    public notify(): void {
        this.eventEmitter.emit();
    }
}
