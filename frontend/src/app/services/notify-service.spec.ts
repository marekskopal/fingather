import { NotifyService } from './notify-service';

class ConcreteNotifyService extends NotifyService {}

describe('NotifyService', () => {
    let service: ConcreteNotifyService;

    beforeEach(() => {
        service = new ConcreteNotifyService();
    });

    it('calls subscriber when notify() is called', () => {
        const callback = vi.fn();
        service.subscribe(callback);

        service.notify();

        expect(callback).toHaveBeenCalledTimes(1);
    });

    it('calls subscriber multiple times for multiple notify() calls', () => {
        const callback = vi.fn();
        service.subscribe(callback);

        service.notify();
        service.notify();
        service.notify();

        expect(callback).toHaveBeenCalledTimes(3);
    });

    it('calls all subscribers when notify() is called', () => {
        const cb1 = vi.fn();
        const cb2 = vi.fn();
        service.subscribe(cb1);
        service.subscribe(cb2);

        service.notify();

        expect(cb1).toHaveBeenCalledTimes(1);
        expect(cb2).toHaveBeenCalledTimes(1);
    });

    it('does not call subscriber before notify() is called', () => {
        const callback = vi.fn();
        service.subscribe(callback);

        expect(callback).not.toHaveBeenCalled();
    });
});
