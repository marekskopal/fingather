import {Component, OnDestroy, OnInit} from '@angular/core';
import { first } from 'rxjs/operators';

import {CurrentUserService, UserService} from '@app/services';
import {User, UserWithStatistic} from "@app/models";
import {NgbModal} from "@ng-bootstrap/ng-bootstrap";
import {AddEditComponent} from "@app/users/add-edit.component";

@Component({ templateUrl: 'list.component.html' })
export class ListComponent implements OnInit, OnDestroy {
    public users: UserWithStatistic[] = [];
    public currentUser: User;

    public constructor(
        private readonly userService: UserService,
        private readonly currentUserService: CurrentUserService,
        private readonly modalService: NgbModal,
    ) {}

    public async ngOnInit(): Promise<void> {
        this.refreshUsers();

        this.currentUser = await this.currentUserService.getCurrentUser();

        this.userService.eventEmitter.subscribe(() => {
            this.refreshUsers();
        });
    }

    public ngOnDestroy(): void {
        this.userService.eventEmitter.unsubscribe();
    }

    public refreshUsers(): void {
        this.userService.getUsers()
            .pipe(first())
            .subscribe(users => this.users = users);
    }

    public addUser(): void {
        this.modalService.open(AddEditComponent);
    }

    public editUser(id: number): void {
        const addEditComponent = this.modalService.open(AddEditComponent);
        addEditComponent.componentInstance.id = id;
    }

    public deleteUser(id: number): void {
        const user = this.users.find(x => x.id === id);
        if (user === undefined) {
            return;
        }
        user.isDeleting = true;
        this.userService.deleteUser(id)
            .pipe(first())
            .subscribe(() => this.users = this.users.filter(x => x.id !== id));
    }
}
