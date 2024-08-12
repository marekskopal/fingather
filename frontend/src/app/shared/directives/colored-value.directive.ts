import {Directive, effect, ElementRef, Input, input, OnInit} from "@angular/core";

@Directive({
    standalone: true,
    selector: '[coloredValue]',
})
export class ColoredValueDirective implements OnInit {
    @Input() coloredValue: number | string;

    constructor(
        private el: ElementRef
    ) {
    }

    ngOnInit(): void {
        if (typeof this.coloredValue === 'string') {
            this.coloredValue = parseFloat(this.coloredValue);
        }

        if (this.coloredValue > 0) {
            this.el.nativeElement.classList.add('green');
        } else if (this.coloredValue < 0) {
            this.el.nativeElement.classList.add('red');
        }
    }
}
