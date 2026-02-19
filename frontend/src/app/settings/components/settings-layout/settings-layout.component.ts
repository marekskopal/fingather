import {ChangeDetectionStrategy, Component, inject, OnInit, signal} from '@angular/core';
import {MatIcon} from '@angular/material/icon';
import {RouterLink, RouterLinkActive, RouterOutlet} from '@angular/router';
import {UserRoleEnum} from '@app/models/enums/user-role-enum';
import {CurrentUserService} from '@app/services';
import {TranslatePipe} from '@ngx-translate/core';

@Component({
    selector: 'fingather-settings-layout',
    templateUrl: 'settings-layout.component.html',
    imports: [
        RouterOutlet,
        RouterLink,
        RouterLinkActive,
        MatIcon,
        TranslatePipe,
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class SettingsLayoutComponent implements OnInit {
    private readonly currentUserService = inject(CurrentUserService);

    protected readonly isAdmin = signal(false);

    public async ngOnInit(): Promise<void> {
        const currentUser = await this.currentUserService.getCurrentUser();
        this.isAdmin.set(currentUser.role === UserRoleEnum.Admin);
    }
}
