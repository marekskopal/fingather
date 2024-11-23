import {
    ChangeDetectionStrategy, Component, input,
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import { NgbTooltip} from '@ng-bootstrap/ng-bootstrap';

@Component({
    selector: 'fingather-help',
    templateUrl: 'help.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    imports: [
        MatIcon,
        NgbTooltip,
    ],
})
export class HelpComponent {
    public text = input.required<string>();
}
