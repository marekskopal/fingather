import {Route} from '@angular/router';
import {OAuthAuthorizeComponent} from '@app/oauth/oauth-authorize/oauth-authorize.component';

export default [
    {
        path: 'authorize',
        component: OAuthAuthorizeComponent,
    },
] satisfies Route[];
