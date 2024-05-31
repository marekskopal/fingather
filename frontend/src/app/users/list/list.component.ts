import {
    ChangeDetectionStrategy, ChangeDetectorRef, Component, OnDestroy, OnInit, signal
} from '@angular/core';
import { User, UserWithStatistic } from '@app/models';
import { CurrentUserService, UserService } from '@app/services';
import { AddEditComponent } from '@app/users/add-edit/add-edit.component';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
    templateUrl: 'list.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ListComponent implements OnInit, OnDestroy {
    private readonly $users = signal<UserWithStatistic[]>([]);
    protected currentUser: User;

    public constructor(
        private readonly userService: UserService,
        private readonly currentUserService: CurrentUserService,
        private readonly modalService: NgbModal,
        private readonly changeDetectorRef: ChangeDetectorRef,
    ) {}

    public async ngOnInit(): Promise<void> {
        this.refreshUsers();

        this.currentUser = await this.currentUserService.getCurrentUser();

        this.userService.subscribe(() => {
            this.refreshUsers();
            this.changeDetectorRef.detectChanges();
        });
    }

    public ngOnDestroy(): void {
        this.userService.unsubscribe();
    }

    protected get users(): UserWithStatistic[] {
        return this.$users();
    }

    private async refreshUsers(): Promise<void> {
        const users = await this.userService.getUsers();
        this.$users.set(users);
    }

    protected addUser(): void {
        this.modalService.open(AddEditComponent);
    }

    protected editUser(id: number): void {
        const addEditComponent = this.modalService.open(AddEditComponent);
        addEditComponent.componentInstance.id = id;
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
