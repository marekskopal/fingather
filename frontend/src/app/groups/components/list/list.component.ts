import { Component, OnDestroy, OnInit } from '@angular/core';
import { AddEditComponent } from '@app/groups/components/add-edit/add-edit.component';
import { Group } from '@app/models';
import { GroupService, PortfolioService } from '@app/services';
import { ConfirmDialogService } from '@app/services/confirm-dialog.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { first } from 'rxjs/operators';

@Component({ templateUrl: 'list.component.html' })
export class ListComponent implements OnInit, OnDestroy {
    public groups: Group[] | null = null;

    public constructor(
        private readonly groupService: GroupService,
        private readonly modalService: NgbModal,
        private readonly portfolioService: PortfolioService,
        private readonly confirmDialogService: ConfirmDialogService,
    ) {}

    public ngOnInit(): void {
        this.refreshGroups();

        this.groupService.eventEmitter.subscribe(() => {
            this.refreshGroups();
        });

        this.portfolioService.eventEmitter.subscribe(() => {
            this.refreshGroups();
        });
    }

    public ngOnDestroy(): void {
        this.groupService.eventEmitter.unsubscribe();
    }

    public async refreshGroups(): Promise<void> {
        this.groups = null;

        const portfolio = await this.portfolioService.getCurrentPortfolio();

        this.groupService.getGroups(portfolio.id)
            .pipe(first())
            .subscribe((groups) => this.groups = groups);
    }

    public addGroup(): void {
        this.modalService.open(AddEditComponent);
    }

    public editGroup(id: number): void {
        const addEditComponent = this.modalService.open(AddEditComponent);
        addEditComponent.componentInstance.id = id;
    }

    public async deleteGroup(id: number): Promise<void> {
        const group = this.groups?.find((x) => x.id === id);
        if (group === undefined) {
            return;
        }
        group.isDeleting = true;

        try {
            const confirmed = await this.confirmDialogService.confirm(
                `Delete group ${group.name}`,
                `Are you sure to delete group ${group.name}?`
            );
            if (!confirmed) {
                group.isDeleting = false;
                return;
            }
        } catch (err) {
            group.isDeleting = false;
            return;
        }

        this.groupService.deleteGroup(id)
            .pipe(first())
            .subscribe(() => this.groups = this.groups !== null
                ? this.groups.filter((x) => x.id !== id)
                : null);
    }
}
