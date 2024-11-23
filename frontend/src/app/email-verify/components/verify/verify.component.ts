import {ChangeDetectionStrategy, Component, inject, OnInit, signal} from '@angular/core';
import {ActivatedRoute, RouterLink} from '@angular/router';
import { EmailVerifyService } from '@app/services';
import { TranslatePipe} from "@ngx-translate/core";

@Component({
    templateUrl: 'verify.component.html',
    imports: [
        TranslatePipe,
        RouterLink,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class VerifyComponent implements OnInit {
    private readonly route = inject(ActivatedRoute);
    private readonly emailVerifyService = inject(EmailVerifyService);

    private token: string;
    protected validated = signal<boolean>(false);

    public async ngOnInit(): Promise<void> {
        this.token = this.route.snapshot.params['token'];

        await this.emailVerifyService.verifyEmail(this.token);

        this.validated.set(true);
    }
}
