import { Asset } from '@app/models/asset';
import { AssetWithProperties } from '@app/models/asset-with-properties';

export interface AssetsWithProperties {
    openAssets: AssetWithProperties[];
    closedAssets: AssetWithProperties[];
    watchedAssets: Asset[];
}
