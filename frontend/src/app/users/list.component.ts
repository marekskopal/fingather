import { Component, OnInit } from '@angular/core';
import { first } from 'rxjs/operators';

import { UserService } from '@app/services';
import {User} from "@app/models";

@Component({ templateUrl: 'list.component.html' })
export class ListComponent implements OnInit {
    public users: User[] = [];

    public constructor(private userService: UserService) {}

    public ngOnInit(): void {
        this.userService.getUsers()
            .pipe(first())
            .subscribe(users => this.users = users);
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
