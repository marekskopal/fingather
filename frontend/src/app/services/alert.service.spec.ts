import { TestBed } from '@angular/core/testing';
import { Alert, AlertType } from '@app/models';

import { AlertService } from './alert.service';

describe('AlertService', () => {
    let service: AlertService;

    beforeEach(() => {
        TestBed.configureTestingModule({ providers: [AlertService] });
        service = TestBed.inject(AlertService);
    });

    describe('onAlert', () => {
        it('emits alerts matching the default id', () => {
            const received: Alert[] = [];
            service.onAlert().subscribe((a) => received.push(a));

            service.success('hello');

            expect(received).toHaveLength(1);
            expect(received[0].message).toBe('hello');
        });

        it('does not emit alerts with a different id', () => {
            const received: Alert[] = [];
            service.onAlert('other-id').subscribe((a) => received.push(a));

            service.success('hello');

            expect(received).toHaveLength(0);
        });

        it('emits alerts matching a custom id', () => {
            const received: Alert[] = [];
            service.onAlert('custom').subscribe((a) => received.push(a));

            service.success('hi', { id: 'custom' });

            expect(received).toHaveLength(1);
        });
    });

    describe('success', () => {
        it('emits an alert with Success type and autoClose=true', () => {
            let received: Alert | undefined;
            service.onAlert().subscribe((a) => { received = a; });

            service.success('ok');

            expect(received?.type).toBe(AlertType.Success);
            expect(received?.message).toBe('ok');
            expect(received?.autoClose).toBe(true);
        });
    });

    describe('error', () => {
        it('emits an alert with Error type', () => {
            let received: Alert | undefined;
            service.onAlert().subscribe((a) => { received = a; });

            service.error('bad');

            expect(received?.type).toBe(AlertType.Error);
            expect(received?.message).toBe('bad');
        });
    });

    describe('info', () => {
        it('emits an alert with Info type and autoClose=true', () => {
            let received: Alert | undefined;
            service.onAlert().subscribe((a) => { received = a; });

            service.info('note');

            expect(received?.type).toBe(AlertType.Info);
            expect(received?.autoClose).toBe(true);
        });
    });

    describe('warning', () => {
        it('emits an alert with Warning type', () => {
            let received: Alert | undefined;
            service.onAlert().subscribe((a) => { received = a; });

            service.warning('careful');

            expect(received?.type).toBe(AlertType.Warning);
            expect(received?.message).toBe('careful');
        });
    });

    describe('clear', () => {
        it('emits an alert with no message (clears default)', () => {
            const received: Alert[] = [];
            service.onAlert().subscribe((a) => received.push(a));

            service.clear();

            expect(received).toHaveLength(1);
            expect(received[0].message).toBeUndefined();
        });

        it('clears a custom alert id', () => {
            const received: Alert[] = [];
            service.onAlert('custom').subscribe((a) => received.push(a));

            service.clear('custom');

            expect(received).toHaveLength(1);
        });
    });
});
