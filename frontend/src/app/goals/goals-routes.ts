import { Route } from '@angular/router';
import { AddEditGoalComponent } from '@app/goals/add-edit-goal/add-edit-goal.component';
import { GoalsComponent } from '@app/goals/goals/goals.component';
import { LayoutComponent } from '@app/shared/components/layout/layout.component';

export default [
    {
        path: '',
        component: LayoutComponent,
        children: [
            {
                path: '',
                component: GoalsComponent,
            },
            {
                path: 'add-goal',
                component: AddEditGoalComponent,
            },
            {
                path: 'edit-goal/:id',
                component: AddEditGoalComponent,
            },
        ],
    },
] satisfies Route[];
