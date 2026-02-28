import {DatePipe} from "@angular/common";
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, inject, OnInit, signal} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {Router, RouterLink} from "@angular/router";
import {User} from '@app/models';
import {OrderDirection} from "@app/models/enums/order-direction";
import {UserOrderBy} from "@app/models/enums/user-order-by";
import {UserRoleEnum} from "@app/models/enums/user-role-enum";
import {UserList} from '@app/models/user-list';
import {CurrentUserService, UserService} from '@app/services';
import {DeleteButtonComponent} from "@app/shared/components/delete-button/delete-button.component";
import {PaginationComponent} from "@app/shared/components/pagination/pagination.component";
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {ScrollShadowDirective} from "@marekskopal/ng-scroll-shadow";
import { TranslatePipe} from "@ngx-translate/core";

@Component({
    templateUrl: 'list.component.html',
    imports: [
        TranslatePipe,
        PortfolioSelectorComponent,
        RouterLink,
        MatIcon,
        DeleteButtonComponent,
        PaginationComponent,
        ScrollShadowDirective,
        DatePipe,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ListComponent implements OnInit {
    private readonly userService = inject(UserService);
    private readonly currentUserService = inject(CurrentUserService);
    private readonly changeDetectorRef = inject(ChangeDetectorRef);
    private readonly router = inject(Router);

    protected readonly users = signal<UserList | null>(null);
    protected currentUser: User;
    protected page = 1;
    protected pageSize = 50;
    protected sortBy: UserOrderBy = UserOrderBy.Id;
    protected sortDirection: OrderDirection = OrderDirection.Desc;

    protected readonly UserOrderBy = UserOrderBy;
    protected readonly OrderDirection = OrderDirection;

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

    private async refreshUsers(): Promise<void> {
        this.users.set(null);
        const userList = await this.userService.getUsers(
            this.pageSize,
            (this.page - 1) * this.pageSize,
            this.sortBy,
            this.sortDirection,
        );
        this.users.set(userList);
    }

    protected async changePage(page: number): Promise<void> {
        this.page = page;
        await this.refreshUsers();
    }

    protected async changePageSize(pageSize: number): Promise<void> {
        this.pageSize = pageSize;
        this.page = 1;
        await this.refreshUsers();
    }

    protected async changeSort(column: UserOrderBy): Promise<void> {
        if (this.sortBy === column) {
            this.sortDirection = this.sortDirection === OrderDirection.Asc
                ? OrderDirection.Desc : OrderDirection.Asc;
        } else {
            this.sortBy = column;
            this.sortDirection = OrderDirection.Desc;
        }
        this.page = 1;
        await this.refreshUsers();
    }

    protected async deleteUser(id: number): Promise<void> {
        const user = this.users()?.users.find((x) => x.id === id);
        if (user === undefined) {
            return;
        }

        await this.userService.deleteUser(id);

        this.users.update((userList) => userList === null ? null : {
            ...userList,
            users: userList.users.filter((x) => x.id !== id),
        });
    }
}
