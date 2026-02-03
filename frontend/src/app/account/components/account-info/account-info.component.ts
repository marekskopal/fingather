import {ChangeDetectionStrategy, Component, inject, OnInit, signal} from '@angular/core';
import {User} from "@app/models";
import {CurrentUserService} from "@app/services";
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {TranslateModule} from "@ngx-translate/core";

@Component({
    templateUrl: 'account-info.component.html',
    standalone: true,
    imports: [
        PortfolioSelectorComponent,
        TranslateModule,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AccountInfoComponent implements OnInit {
    private readonly currentUserService = inject(CurrentUserService);

    protected readonly $user = signal<User | null>(null);

    public async ngOnInit(): Promise<void> {
        await this.refreshCurrentUser();
    }

    protected async onEmailNotificationsChange(): Promise<void> {
        const user = this.$user();
        if (user === null) {
            return;
        }

        const updatedUser = await this.currentUserService.updateCurrentUser({
            isEmailNotificationsEnabled: !user.isEmailNotificationsEnabled,
        });
        this.$user.set(updatedUser);
    }

    private async refreshCurrentUser(): Promise<void> {
        const currentUser = await this.currentUserService.getCurrentUser();
        this.$user.set(currentUser);
    }
}
