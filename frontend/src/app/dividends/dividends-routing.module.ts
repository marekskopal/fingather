import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { DividendsHistoryComponent } from '@app/dividends/components/history/dividends-history.component';
import { LayoutComponent } from '@app/dividends/components/layout/layout.component';

const routes: Routes = [
    {
        path: '',
        component: LayoutComponent,
        children: [
            { path: '', component: DividendsHistoryComponent },
        ]
    }
];

@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule]
})
export class DividendsRoutingModule { }
