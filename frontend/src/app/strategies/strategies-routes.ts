import { Route } from '@angular/router';
import { LayoutComponent } from '@app/shared/components/layout/layout.component';
import { AddEditStrategyComponent } from '@app/strategies/components/add-edit/add-edit-strategy.component';
import { StrategiesComponent } from '@app/strategies/components/strategies/strategies.component';

export default [
    {
        path: '',
        component: LayoutComponent,
        children: [
            {
                path: '',
                component: StrategiesComponent,
            },
            {
                path: 'add-strategy',
                component: AddEditStrategyComponent,
            },
            {
                path: 'edit-strategy/:id',
                component: AddEditStrategyComponent,
            },
        ],
    },
] satisfies Route[];
