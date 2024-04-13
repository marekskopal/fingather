import {Component, Input} from '@angular/core';

@Component({
    selector: 'fingather-table-value',
    templateUrl: 'table-value.component.html'
})
export class TableValueComponent {
    @Input() public value: number|null;
}
