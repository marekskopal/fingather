import {Route} from '@angular/router';
import {BenchmarkAssetsComponent} from '@app/settings/components/benchmark-assets/benchmark-assets.component';
import {LayoutComponent} from '@app/shared/components/layout/layout.component';

export default [
    {
        path: '',
        component: LayoutComponent,
        children: [
            {
                path: '',
                redirectTo: 'benchmark-assets',
                pathMatch: 'full',
            },
            {
                path: 'benchmark-assets',
                component: BenchmarkAssetsComponent,
            },
        ],
    },
] satisfies Route[];
