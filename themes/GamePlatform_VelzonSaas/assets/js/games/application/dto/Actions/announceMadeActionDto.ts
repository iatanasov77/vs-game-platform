import ActionDto from './actionDto';

import AnnounceDto from '_@/GamePlatform/Model/CardGame/announceDto';

interface AnnounceMadeActionDto extends ActionDto {
    announce: AnnounceDto;
}

export default AnnounceMadeActionDto;