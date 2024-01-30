import { NgModule } from '@angular/core';
import { RouterModule,Routes } from '@angular/router';

import { ImportComponent } from './import.component';
import { LayoutComponent } from './layout.component';

const routes: Routes = [
    {
        path: '', component: LayoutComponent,
        children: [
            { path: '', component: ImportComponent }
        ]
    }
];

@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule]
})
export class ImportRoutingModule { }
