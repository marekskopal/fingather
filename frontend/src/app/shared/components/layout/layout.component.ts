import { ChangeDetectionStrategy, Component } from '@angular/core';
import { RouterOutlet } from "@angular/router";

@Component({
    templateUrl: 'layout.component.html',
    standalone: true,
    imports: [
        RouterOutlet
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class LayoutComponent { }
