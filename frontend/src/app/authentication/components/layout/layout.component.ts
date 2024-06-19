import { ChangeDetectionStrategy, Component } from '@angular/core';
import { Router } from '@angular/router';
import { AuthenticationService } from '@app/services/authentication.service';

@Component({
    templateUrl: 'layout.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class LayoutComponent {
    public constructor(
        private router: Router,
        private authenticationService: AuthenticationService
    ) {
        if (this.authenticationService.$isLoggedIn()) {
            this.router.navigate(['/']);
        }
    }
}
