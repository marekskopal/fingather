import {
    ChangeDetectionStrategy, ChangeDetectorRef, Component, inject, OnInit, signal
} from '@angular/core';
import { Group } from '@app/models';
import { GroupService, PortfolioService } from '@app/services';

@Component({
    templateUrl: 'list.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ListComponent implements OnInit {
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
