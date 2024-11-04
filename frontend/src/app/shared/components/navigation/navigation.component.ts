import {NgOptimizedImage} from "@angular/common";
import {
    ChangeDetectionStrategy, Component, inject, OnInit, signal,
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {RouterLink, RouterLinkActive} from "@angular/router";
import {User} from "@app/models";
import {UserRoleEnum} from "@app/models/enums/user-role-enum";
import {CurrentUserService} from "@app/services";
import {AuthenticationService} from "@app/services/authentication.service";
import {LanguageSelectorComponent} from "@app/shared/components/language-selector/language-selector.component";
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {NgbCollapse} from "@ng-bootstrap/ng-bootstrap";
import { TranslatePipe} from "@ngx-translate/core";

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
        TranslatePipe,
        LanguageSelectorComponent,
        PortfolioSelectorComponent
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class NavigationComponent implements OnInit {
    private readonly authenticationService = inject(AuthenticationService);
    private readonly currentUserService = inject(CurrentUserService);

    protected isNavigationCollapsed: boolean = true;

    protected $currentUser = signal<User | null>(null);

    public async ngOnInit(): Promise<void> {
        const currentUser = await this.currentUserService.getCurrentUser();
        this.$currentUser.set(currentUser);
    }

    public logout(): void {
        this.authenticationService.logout();
    }

    protected toggleNavigation(): void {
        this.isNavigationCollapsed = !this.isNavigationCollapsed;
    }

    protected collapseNavigation(): void {
        this.isNavigationCollapsed = true;
    }

    protected readonly UserRoleEnum = UserRoleEnum;
}
