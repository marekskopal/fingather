import {
    ChangeDetectionStrategy, Component, inject,
} from '@angular/core';
import {AuthenticationService} from "@app/services/authentication.service";

@Component({
    selector: 'fingather-navigation',
    templateUrl: 'navigation.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class NavigationComponent {
    private readonly authenticationService = inject(AuthenticationService);

    protected isNavigationCollapsed: boolean = true;

    public logout(): void {
        this.authenticationService.logout();
    }

    protected toggleNavigation(): void {
        this.isNavigationCollapsed = !this.isNavigationCollapsed;
    }

    protected collapseNavigation(): void {
        this.isNavigationCollapsed = true;
    }
}
