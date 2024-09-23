import {
    ChangeDetectionStrategy, Component, input
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import { NgbTooltip} from '@ng-bootstrap/ng-bootstrap';
import {TranslateModule} from "@ngx-translate/core";

@Component({
    selector: 'fingather-help',
    templateUrl: 'help.component.html',
    standalone: true,
    changeDetection: ChangeDetectionStrategy.OnPush,
    imports: [
        MatIcon,
        TranslateModule,
        NgbTooltip
    ]
})
export class HelpComponent {
    public $text = input.required<string>({
        alias: 'text',
    });
}
