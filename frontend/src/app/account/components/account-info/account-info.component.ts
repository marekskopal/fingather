import {DatePipe} from "@angular/common";
import {ChangeDetectionStrategy, Component, inject, OnInit, signal} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {RouterLink} from "@angular/router";
import {User} from "@app/models";
import {CurrentUserService} from "@app/services";
import {DeleteButtonComponent} from "@app/shared/components/delete-button/delete-button.component";
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {TranslateModule} from "@ngx-translate/core";

@Component({
    templateUrl: 'account-info.component.html',
    standalone: true,
    imports: [
        PortfolioSelectorComponent,
        TranslateModule,
        DatePipe,
        MatIcon,
        RouterLink,
        DeleteButtonComponent,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AccountInfoComponent implements OnInit {
    private readonly currentUserService = inject(CurrentUserService);

    protected readonly $user = signal<User | null>(null);

    public async ngOnInit(): Promise<void> {
        await this.refreshCurrentUser();
    }

    private async refreshCurrentUser(): Promise<void> {
        const currentUser = await this.currentUserService.getCurrentUser();
        this.$user.set(currentUser);
    }
}
