import { DestroyRef } from '@angular/core';

import { NotifyService } from './notify-service';

class ConcreteNotifyService extends NotifyService {}

class MockDestroyRef extends DestroyRef {
    public override onDestroy(_callback: () => void): () => void {
        return () => {};
    }
}

describe('NotifyService', () => {
    let service: ConcreteNotifyService;
    let destroyRef: MockDestroyRef;

    beforeEach(() => {
        service = new ConcreteNotifyService();
        destroyRef = new MockDestroyRef();
    });

    it('calls subscriber when notify() is called', () => {
        const callback = vi.fn();
        service.subscribe(callback, destroyRef);

        service.notify();

        expect(callback).toHaveBeenCalledTimes(1);
    });

    it('calls subscriber multiple times for multiple notify() calls', () => {
        const callback = vi.fn();
        service.subscribe(callback, destroyRef);

        service.notify();
        service.notify();
        service.notify();

        expect(callback).toHaveBeenCalledTimes(3);
    });

    it('calls all subscribers when notify() is called', () => {
        const cb1 = vi.fn();
        const cb2 = vi.fn();
        service.subscribe(cb1, destroyRef);
        service.subscribe(cb2, destroyRef);

        service.notify();

        expect(cb1).toHaveBeenCalledTimes(1);
        expect(cb2).toHaveBeenCalledTimes(1);
    });

    it('does not call subscriber before notify() is called', () => {
        const callback = vi.fn();
        service.subscribe(callback, destroyRef);

        expect(callback).not.toHaveBeenCalled();
    });
});
