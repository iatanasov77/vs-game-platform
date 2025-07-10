<?php namespace App\Component\Dto\toplist;

use Doctrine\Common\Collections\Collection;

class Toplist
{
    public Collection $results; // ToplistResult[]
    public ToplistResult $you;
}
