import {Route} from '@angular/router';
import {AddEditGroupComponent} from "@app/groups/components/add-edit/add-edit-group.component";
import { ListComponent } from '@app/groups/components/list/list.component';
import {LayoutComponent} from "@app/shared/components/layout/layout.component";

export default [
    {
        path: '',
        component: LayoutComponent,
        children: [
            {
                path: '',
                component: ListComponent
            },
            {
                path: 'add-group',
                component: AddEditGroupComponent
            },
            {
                path: 'edit-group/:id',
                component: AddEditGroupComponent
            },
        ]
    }
] satisfies Route[];
