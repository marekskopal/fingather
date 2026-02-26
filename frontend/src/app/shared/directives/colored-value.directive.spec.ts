import {ChangeDetectionStrategy, Component} from '@angular/core';
import {ComponentFixture, TestBed} from '@angular/core/testing';

import { ColoredValueDirective } from './colored-value.directive';

@Component({
    standalone: true,
    imports: [ColoredValueDirective],
    changeDetection: ChangeDetectionStrategy.OnPush,
    template: `<div [coloredValue]="value"></div>`,
})
class TestHostComponent {
    public value: number | string | null = 0;
}

function createFixture(value: number | string | null): ComponentFixture<TestHostComponent> {
    TestBed.configureTestingModule({
        imports: [TestHostComponent],
    });
    const fixture = TestBed.createComponent(TestHostComponent);
    fixture.componentInstance.value = value;
    fixture.detectChanges();
    return fixture;
}

describe('ColoredValueDirective', () => {
    it('adds "green" class for a positive number', () => {
        const fixture = createFixture(5);
        const div: HTMLElement = fixture.nativeElement.querySelector('div');
        expect(div.classList.contains('green')).toBe(true);
        expect(div.classList.contains('red')).toBe(false);
    });

    it('adds "red" class for a negative number', () => {
        const fixture = createFixture(-3);
        const div: HTMLElement = fixture.nativeElement.querySelector('div');
        expect(div.classList.contains('red')).toBe(true);
        expect(div.classList.contains('green')).toBe(false);
    });

    it('adds neither class for zero', () => {
        const fixture = createFixture(0);
        const div: HTMLElement = fixture.nativeElement.querySelector('div');
        expect(div.classList.contains('green')).toBe(false);
        expect(div.classList.contains('red')).toBe(false);
    });

    it('adds neither class for null', () => {
        const fixture = createFixture(null);
        const div: HTMLElement = fixture.nativeElement.querySelector('div');
        expect(div.classList.contains('green')).toBe(false);
        expect(div.classList.contains('red')).toBe(false);
    });

    it('parses a positive string and adds "green" class', () => {
        const fixture = createFixture('7.5');
        const div: HTMLElement = fixture.nativeElement.querySelector('div');
        expect(div.classList.contains('green')).toBe(true);
    });

    it('parses a negative string and adds "red" class', () => {
        const fixture = createFixture('-2.1');
        const div: HTMLElement = fixture.nativeElement.querySelector('div');
        expect(div.classList.contains('red')).toBe(true);
    });
});
