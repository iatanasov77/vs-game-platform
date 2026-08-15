import ActionDto from './actionDto';

import { AnnounceDto } from '@vankosoft/game-platform';

interface AnnounceMadeActionDto extends ActionDto {
    announce: AnnounceDto;
}

export default AnnounceMadeActionDto;