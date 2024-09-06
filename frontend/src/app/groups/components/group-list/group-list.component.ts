import {
    ChangeDetectionStrategy, ChangeDetectorRef, Component, inject, OnInit, signal
} from '@angular/core';
import {MatIcon} from "@angular/material/icon";
import {RouterLink} from "@angular/router";
import { Group } from '@app/models';
import { GroupService, PortfolioService } from '@app/services';
import {DeleteButtonComponent} from "@app/shared/components/delete-button/delete-button.component";
import {PortfolioSelectorComponent} from "@app/shared/components/portfolio-selector/portfolio-selector.component";
import {TranslateModule} from "@ngx-translate/core";

@Component({
    templateUrl: 'group-list.component.html',
    standalone: true,
    imports: [
        TranslateModule,
        PortfolioSelectorComponent,
        RouterLink,
        MatIcon,
        DeleteButtonComponent
    ],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class GroupListComponent implements OnInit {
    private readonly groupService = inject(GroupService);
    private readonly portfolioService = inject(PortfolioService);
    private readonly changeDetectorRef = inject(ChangeDetectorRef);

    public readonly $groups = signal<Group[] | null>(null);

    public ngOnInit(): void {
        this.refreshGroups();

        this.groupService.subscribe(() => {
            this.refreshGroups();
            this.changeDetectorRef.detectChanges();
        });

        this.portfolioService.subscribe(() => {
            this.refreshGroups();
            this.changeDetectorRef.detectChanges();
        });
    }

    protected get groups(): Group[] | null {
        return this.$groups();
    }

    private async refreshGroups(): Promise<void> {
        this.$groups.set(null);

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        const groups = await this.groupService.getGroups(portfolio.id);
        this.$groups.set(groups);
    }

    protected async deleteGroup(id: number): Promise<void> {
        const group = this.$groups()?.find((x) => x.id === id);
        if (group === undefined) {
            return;
        }

        await this.groupService.deleteGroup(id);

        this.$groups.update((groups) => (groups !== null
            ? groups.filter((x) => x.id !== id)
            : null));
    }
}
