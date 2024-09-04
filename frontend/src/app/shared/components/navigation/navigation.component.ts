import {NgOptimizedImage} from "@angular/common";
import {
    ChangeDetectionStrategy, Component, inject,
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {RouterLink, RouterLinkActive} from "@angular/router";
import {AuthenticationService} from "@app/services/authentication.service";
import {LanguageSelectorComponent} from "@app/shared/components/language-selector/language-selector.component";
import {NgbCollapse} from "@ng-bootstrap/ng-bootstrap";
import {TranslateModule} from "@ngx-translate/core";

@Component({
    selector: 'fingather-navigation',
    templateUrl: 'navigation.component.html',
    standalone: true,
    imports: [
        RouterLink,
        RouterLinkActive,
        NgOptimizedImage,
        NgbCollapse,
        MatIcon,
        TranslateModule,
        LanguageSelectorComponent
    ],
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
