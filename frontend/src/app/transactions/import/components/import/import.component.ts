import { ChangeDetectionStrategy, Component } from '@angular/core';

@Component({
    templateUrl: 'import.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ImportComponent {
}
