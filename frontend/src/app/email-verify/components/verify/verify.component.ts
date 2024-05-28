import { ChangeDetectionStrategy, Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { EmailVerifyService } from '@app/services';

@Component({
    templateUrl: 'verify.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class VerifyComponent implements OnInit {
    private token: string;
    public validated: boolean = false;

    public constructor(
        private readonly route: ActivatedRoute,
        private readonly emailVerifyService: EmailVerifyService,
    ) {}

    public async ngOnInit(): Promise<void> {
        this.token = this.route.snapshot.params['token'];

        await this.emailVerifyService.verifyEmail(this.token);

        this.validated = true;
    }
}
