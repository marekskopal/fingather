import { Route } from '@angular/router';
import { AddEditDcaPlanComponent } from '@app/dca-plans/components/add-edit-dca-plan/add-edit-dca-plan.component';
import { DcaPlanDetailComponent } from '@app/dca-plans/components/dca-plan-detail/dca-plan-detail.component';
import { DcaPlansComponent } from '@app/dca-plans/components/dca-plans/dca-plans.component';
import { LayoutComponent } from '@app/shared/components/layout/layout.component';

export default [
    {
        path: '',
        component: LayoutComponent,
        children: [
            {
                path: '',
                component: DcaPlansComponent,
            },
            {
                path: 'add-dca-plan',
                component: AddEditDcaPlanComponent,
            },
            {
                path: 'edit-dca-plan/:id',
                component: AddEditDcaPlanComponent,
            },
            {
                path: ':id',
                component: DcaPlanDetailComponent,
            },
        ],
    },
] satisfies Route[];
