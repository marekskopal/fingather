import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import {AddEditUserComponent} from "@app/users/add-edit/add-edit-user.component";
import { LayoutComponent } from '@app/users/layout/layout.component';
import { ListComponent } from '@app/users/list/list.component';

const routes: Routes = [
    {
        path: '',
        component: LayoutComponent,
        children: [
            { path: '', component: ListComponent },
            { path: 'add-user', component: AddEditUserComponent },
            { path: 'edit-user/:id', component: AddEditUserComponent },
        ]
    }
];

@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule]
})
export class UsersRoutingModule { }
