import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import {AddEditGroupComponent} from "@app/groups/components/add-edit/add-edit-group.component";
import { LayoutComponent } from '@app/groups/components/layout/layout.component';
import { ListComponent } from '@app/groups/components/list/list.component';

const routes: Routes = [
    {
        path: '',
        component: LayoutComponent,
        children: [
            { path: '', component: ListComponent },
            { path: 'add-group', component: AddEditGroupComponent },
            { path: 'edit-group/:id', component: AddEditGroupComponent },
        ]
    }
];

@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule]
})
export class GroupsRoutingModule { }
