import {Route} from '@angular/router';
import {AddMcpApiKeyComponent} from '@app/mcp-api-keys/add-mcp-api-key/add-mcp-api-key.component';
import {McpApiKeysComponent} from '@app/mcp-api-keys/mcp-api-keys/mcp-api-keys.component';

export default [
    {
        path: '',
        component: McpApiKeysComponent,
    },
    {
        path: 'add-mcp-api-key',
        component: AddMcpApiKeyComponent,
    },
] satisfies Route[];
