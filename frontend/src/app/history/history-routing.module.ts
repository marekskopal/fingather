import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import {LayoutComponent} from "@app/history/components/layout/layout.component";
import {HistoryComponent} from "@app/history/components/history/history.component";


const routes: Routes = [
    {
        path: '', component: LayoutComponent,
        children: [
            { path: '', component: HistoryComponent },
        ]
    }
];

@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule]
})
export class HistoryRoutingModule { }
