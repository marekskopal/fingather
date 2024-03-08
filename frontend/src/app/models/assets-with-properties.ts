import { Asset } from '@app/models/asset';
import { AssetWithProperties } from '@app/models/asset-with-properties';

export class AssetsWithProperties {
    public openAssets: AssetWithProperties[];
    public closedAssets: AssetWithProperties[];
    public watchedAssets: Asset[];
}
