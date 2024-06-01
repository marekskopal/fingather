import {
    ChangeDetectionStrategy, ChangeDetectorRef, Component, OnInit, signal
} from '@angular/core';
import { AddEditComponent } from '@app/groups/components/add-edit/add-edit.component';
import { Group } from '@app/models';
import { GroupService, PortfolioService } from '@app/services';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
    templateUrl: 'list.component.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ListComponent implements OnInit {
    public readonly $groups = signal<Group[] | null>(null);

    public constructor(
        private readonly groupService: GroupService,
        private readonly modalService: NgbModal,
        private readonly portfolioService: PortfolioService,
        private readonly changeDetectorRef: ChangeDetectorRef,
    ) {}

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

    protected addGroup(): void {
        this.modalService.open(AddEditComponent);
    }

    protected editGroup(id: number): void {
        const addEditComponent = this.modalService.open(AddEditComponent);
        addEditComponent.componentInstance.id.set(id);
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
