import {Directive, ElementRef, inject, input, OnInit, Renderer2} from "@angular/core";
import {TableGridColumn} from "@app/shared/types/table-grid-column";

@Directive({
    standalone: true,
    //eslint-disable-next-line @angular-eslint/directive-selector
    selector: '[tableGrid]',
})
export class TableGridDirective implements OnInit {
    private readonly el = inject(ElementRef);
    private readonly renderer = inject(Renderer2);

    public $columns = input.required<TableGridColumn[]>({
        alias: 'columns',
    });

    public ngOnInit(): void {
        this.renderer.addClass(this.el.nativeElement, 'table-grid');

        const gridTemplateColumns = this.$columns().map(
            column => `minmax(${column.min}, ${column.max})`
        );

        this.renderer.setStyle(
            this.el.nativeElement,
            'grid-template-columns',
            gridTemplateColumns.join(' ')
        );
    }
}
