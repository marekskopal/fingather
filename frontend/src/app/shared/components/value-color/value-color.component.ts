import {Component, Input} from '@angular/core';

@Component({
    selector: 'fingather-value-color',
    templateUrl: 'value-color.component.html'
})
export class ValueColorComponent {
    @Input() public value: number;
}
