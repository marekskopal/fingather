import {Directive, ElementRef, Input, OnInit} from "@angular/core";

@Directive({
    standalone: true,
    //eslint-disable-next-line @angular-eslint/directive-selector
    selector: '[coloredValue]',
})
export class ColoredValueDirective implements OnInit {
    @Input() public coloredValue: number | string | null;

    public constructor(
        private el: ElementRef
    ) {
    }

    public ngOnInit(): void {
        if (typeof this.coloredValue === 'string') {
            this.coloredValue = parseFloat(this.coloredValue);
        }

        if (this.coloredValue === null) {
            return;
        }

        if (this.coloredValue > 0) {
            this.el.nativeElement.classList.add('green');
        } else if (this.coloredValue < 0) {
            this.el.nativeElement.classList.add('red');
        }
    }
}
