<?php namespace App\Component\AI\BoardGame;

class Config
{
    /**
     * Blots are hitables checkers. Below this threshold does not reduce score.
     * 
     * @var int
     */
    public $BlotsThreshold;
    
    /**
     * The point divided by this factor reduces score for blots.
     * 
     * @var float
     */
    public $BlotsFactor;
    
    /**
     * The point divided by this factor reduces score for blots. When opponent has passes this point with all checker.
     * 
     * @var float
     */
    public $BlotsFactorPassed;
    
    /**
     * Score received for one point blocked.
     * 
     * @var float
     */
    public $BlockedPointScore;
    
    /**
     * Score from number of consecutive blocks raised to this value.
     * 
     * @var float
     */
    public $ConnectedBlocksFactor;
    
    /**
     * When all checkers have passed each other, the leading side gets a score bonus
     * this factor multiplied by the lead.
     * 
     * @var float
     */
    public $RunOrBlockFactor;
    
    /** @var bool */
    public $ProbablityScore = false;
    
    //     public function __toString(): string
    //     {
    //         return "BF: {$this->BlotsFactor}  BFP: {$this->BlotsFactorPassed}  BT: {$this->BlotsThreshold}  CB: {$this->ConnectedBlocksFactor}  BP: {$this->BlockedPointScore}  RB: {$this->RunOrBlockFactor}";
    //     }
        
    public static function Untrained(): Config
    {
        $config = new Config();
        $config->BlotsFactor = 1;
        $config->BlotsFactorPassed = 1;
        $config->BlotsThreshold = 0;
        $config->BlockedPointScore = 0;
        $config->ConnectedBlocksFactor = 0;
        $config->ProbablityScore = false;
        $config->RunOrBlockFactor = 0;
        
        return $config;
    }
    
    public static function Trained(): Config
    {
        $config = new Config();
        $config->BlotsFactor = 1.225;
        $config->BlotsFactorPassed = 1.925;
        $config->BlotsThreshold = 3;
        $config->BlockedPointScore = 1.608432;
        $config->ConnectedBlocksFactor = 0.739531007;
        $config->ProbablityScore = false;
        $config->RunOrBlockFactor = 0.262721103;
        
        return $config;
    }
    
    public static function NoDoubles41Epochs(): Config
    {
        $config = new Config();
        $config->BlotsFactor = 1.747286362;
        $config->BlotsThreshold = 14;
        $config->BlockedPointScore = 1.145912;
        $config->ConnectedBlocksFactor = 2.019573916;
        $config->ProbablityScore = false;
        $config->RunOrBlockFactor = 0.838315223;
        
        return $config;
    }
    
    public static function NoDoubles20Epochs(): Config
    {
        $config = new Config();
        $config->BlotsFactor = 1.699189048;
        $config->BlotsThreshold = 6;
        $config->BlockedPointScore = 1.145912;
        $config->ConnectedBlocksFactor = 1.979573916;
        $config->ProbablityScore = false;
        $config->RunOrBlockFactor = 1.358788917;
        
        return $config;
    }
    
    public static function NoDoubles8Epochs(): Config
    {
        $config = new Config();
        $config->BlotsFactor = 1.27486242;
        $config->BlotsThreshold = 3;
        $config->BlockedPointScore = 0;
        $config->ConnectedBlocksFactor = 0.762388608;
        $config->ProbablityScore = false;
        $config->RunOrBlockFactor = 0.452949965;
        
        return $config;
    }
}
