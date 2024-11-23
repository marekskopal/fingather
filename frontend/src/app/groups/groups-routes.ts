import {Route} from '@angular/router';
import {AddEditGroupComponent} from "@app/groups/components/add-edit/add-edit-group.component";
import { GroupListComponent } from '@app/groups/components/group-list/group-list.component';
import {LayoutComponent} from "@app/shared/components/layout/layout.component";

export default [
    {
        path: '',
        component: LayoutComponent,
        children: [
            {
                path: '',
                component: GroupListComponent,
            },
            {
                path: 'add-group',
                component: AddEditGroupComponent,
            },
            {
                path: 'edit-group/:id',
                component: AddEditGroupComponent,
            },
        ],
    },
] satisfies Route[];
