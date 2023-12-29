import { Component, OnInit } from '@angular/core';
import { first } from 'rxjs/operators';

import { UserService } from '@app/services';
import {User} from "@app/models";

@Component({ templateUrl: 'list.component.html' })
export class ListComponent implements OnInit {
    public users: User[] = [];

    constructor(private userService: UserService) {}

    ngOnInit() {
        this.userService.getAll()
            .pipe(first())
            .subscribe(users => this.users = users);
    }

    deleteUser(id: number) {
        const user = this.users.find(x => x.id === id);
        if (user === undefined) {
            return;
        }
        user.isDeleting = true;
        this.userService.delete(id)
            .pipe(first())
            .subscribe(() => this.users = this.users.filter(x => x.id !== id));
    }
}
