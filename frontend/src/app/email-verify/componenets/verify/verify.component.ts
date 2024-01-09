import {Component, OnInit} from '@angular/core';
import {first} from "rxjs/operators";
import {ActivatedRoute, Router} from "@angular/router";
import {EmailVerifyService} from "@app/services";

@Component({ templateUrl: 'verify.component.html' })
export class VerifyComponent implements OnInit {
    private token: string;
    public validated: boolean = false;

    public constructor(
        private readonly route: ActivatedRoute,
        private readonly router: Router,
        private readonly emailVerifyService: EmailVerifyService,
    ) {}

    public ngOnInit(): void {
        this.token = this.route.snapshot.params['token'];

        this.emailVerifyService.verifyEmail(this.token)
            .pipe(first())
            .subscribe(() => {this.validated = true});
    }
}
