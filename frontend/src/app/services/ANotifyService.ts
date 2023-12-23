import { EventEmitter } from "@angular/core";

export abstract class ANotifyService {
    public eventEmitter: EventEmitter<null> = new EventEmitter();

    public notify(): void {
        this.eventEmitter.emit();
    }
}