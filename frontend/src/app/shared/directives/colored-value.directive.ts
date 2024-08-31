import {Directive, ElementRef, inject, input, OnInit} from "@angular/core";

@Directive({
    standalone: true,
    //eslint-disable-next-line @angular-eslint/directive-selector
    selector: '[coloredValue]',
})
export class ColoredValueDirective implements OnInit {
    private readonly el = inject(ElementRef);

    public $coloredValue = input.required<number | string | null>({
        alias: 'coloredValue',
    });

    public ngOnInit(): void {
        let coloredValue = this.$coloredValue();

        if (typeof coloredValue === 'string') {
            coloredValue = parseFloat(coloredValue);
        }

        if (coloredValue === null) {
            return;
        }

        if (coloredValue > 0) {
            this.el.nativeElement.classList.add('green');
        } else if (coloredValue < 0) {
            this.el.nativeElement.classList.add('red');
        }
    }
}
