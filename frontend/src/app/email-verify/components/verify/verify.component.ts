import {ChangeDetectionStrategy, Component, inject, OnInit, signal} from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { EmailVerifyService } from '@app/services';

@Component({
    templateUrl: 'verify.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class VerifyComponent implements OnInit {
    private readonly route = inject(ActivatedRoute);
    private readonly emailVerifyService = inject(EmailVerifyService);

    private token: string;
    protected $validated = signal<boolean>(false);

    public async ngOnInit(): Promise<void> {
        this.token = this.route.snapshot.params['token'];

        await this.emailVerifyService.verifyEmail(this.token);

        this.$validated.set(true);
    }
}
