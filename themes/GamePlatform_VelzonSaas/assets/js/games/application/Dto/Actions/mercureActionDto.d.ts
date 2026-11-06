import { MercureActionNames } from '../../Enum/mercureActionNames';

export interface MercureActionDto {
    actionName: MercureActionNames;
    gameType?: string;
}
