import {computed, signal} from '@angular/core';
import {BaseForm} from "@app/shared/components/form/base-form";

export abstract class BaseAddEditForm extends BaseForm {
    protected readonly $id = signal<number | null>(null);

    protected readonly $routerBackLink = computed<string>(() => {
        return this.$id() !== null ? '../..' : '..';
    });
}
