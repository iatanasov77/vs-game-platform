<?php namespace App\Component\Dto\Actions;

use App\Component\Dto\AnnounceDto;

class AnnounceMadeActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::announceMade->value;
    }
    
    /** @var AnnounceDto $announce */
    public AnnounceDto $announce;
}
