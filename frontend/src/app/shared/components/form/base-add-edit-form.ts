import {computed, inject, signal} from '@angular/core';
import {ActivatedRoute} from "@angular/router";
import {BaseForm} from "@app/shared/components/form/base-form";

export abstract class BaseAddEditForm extends BaseForm {
    protected readonly route = inject(ActivatedRoute);

    protected readonly $id = signal<number | null>(null);

    protected readonly $routerBackLink = computed<string>(() => {
        return this.$id() !== null ? '../..' : '..';
    });

    protected initializeIdFromRoute(): void {
        if (this.route.snapshot.params['id'] !== undefined) {
            this.$id.set(parseInt(this.route.snapshot.params['id'], 10));
        }
    }
}
