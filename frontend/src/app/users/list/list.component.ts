import {ChangeDetectionStrategy, ChangeDetectorRef, Component, inject, OnInit, signal} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {Router, RouterLink} from "@angular/router";
import {User, UserWithStatistic} from '@app/models';
import {UserRoleEnum} from "@app/models/enums/user-role-enum";
import {CurrentUserService, UserService} from '@app/services';
import {DeleteButtonComponent} from "@app/shared/components/delete-button/delete-button.component";
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {ScrollShadowDirective} from "@marekskopal/ng-scroll-shadow";
import {TranslateModule} from "@ngx-translate/core";

@Component({
    templateUrl: 'list.component.html',
    standalone: true,
    imports: [
        TranslateModule,
        PortfolioSelectorComponent,
        RouterLink,
        MatIcon,
        DeleteButtonComponent,
        ScrollShadowDirective
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ListComponent implements OnInit {
    private readonly userService = inject(UserService);
    private readonly currentUserService = inject(CurrentUserService);
    private readonly changeDetectorRef = inject(ChangeDetectorRef);
    private readonly router = inject(Router);

    private readonly $users = signal<UserWithStatistic[]>([]);
    protected currentUser: User;

    public async ngOnInit(): Promise<void> {
        this.currentUser = await this.currentUserService.getCurrentUser();
        if (this.currentUser.role !== UserRoleEnum.Admin) {
            this.router.navigate(['/']);
        }

        this.refreshUsers();

        this.userService.subscribe(() => {
            this.refreshUsers();
            this.changeDetectorRef.detectChanges();
        });
    }

    protected get users(): UserWithStatistic[] {
        return this.$users();
    }

    private async refreshUsers(): Promise<void> {
        const users = await this.userService.getUsers();
        this.$users.set(users);
    }

    protected async deleteUser(id: number): Promise<void> {
        const user = this.$users().find((x) => x.id === id);
        if (user === undefined) {
            return;
        }

        await this.userService.deleteUser(id);

        this.$users.update((users) => users.filter((x) => x.id !== id));
    }
}
