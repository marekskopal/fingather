import {ChangeDetectionStrategy, Component, effect, inject} from '@angular/core';
import { Router } from '@angular/router';
import { AuthenticationService } from '@app/services/authentication.service';

@Component({
    templateUrl: 'layout.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class LayoutComponent {
    private router = inject(Router);
    private authenticationService = inject(AuthenticationService);

    public constructor() {
        effect(() => {
            if (this.authenticationService.$isLoggedIn()) {
                this.router.navigate(['/']);
            }
        });
    }
}
