import {Route} from '@angular/router';
import {LayoutComponent} from "@app/shared/components/layout/layout.component";
import {AddEditUserComponent} from "@app/users/add-edit/add-edit-user.component";
import { ListComponent } from '@app/users/list/list.component';

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
                path: 'add-user',
                component: AddEditUserComponent
            },
            {
                path: 'edit-user/:id',
                component: AddEditUserComponent
            },
        ]
    }
] satisfies Route[];
