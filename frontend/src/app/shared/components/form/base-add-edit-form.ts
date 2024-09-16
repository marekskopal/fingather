import {computed, inject, signal} from '@angular/core';
import {BaseForm} from "@app/shared/components/form/base-form";
import {ActivatedRoute} from "@angular/router";

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
