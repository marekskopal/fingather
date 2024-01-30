import { NgModule } from '@angular/core';
import { RouterModule,Routes } from '@angular/router';
import {HistoryComponent} from '@app/history/components/history/history.component';
import {LayoutComponent} from '@app/history/components/layout/layout.component';


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
