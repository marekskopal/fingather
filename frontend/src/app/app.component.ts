import {ChangeDetectionStrategy, Component, computed, inject} from '@angular/core';
import { AuthenticationService } from '@app/services/authentication.service';

@Component({
    selector: 'fingather-app',
    templateUrl: 'app.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AppComponent {
    private readonly authenticationService = inject(AuthenticationService);

    protected $isLoggedIn = computed<boolean>(() => this.authenticationService.$isLoggedIn());
}
