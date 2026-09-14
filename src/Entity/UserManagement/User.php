<?php namespace App\Entity\UserManagement;

use Doctrine\ORM\Mapping as ORM;
use Vankosoft\UsersBundle\Model\User as BaseUser;

use Vankosoft\UsersSubscriptionsBundle\Model\Interfaces\SubscribedUserInterface;
use Vankosoft\UsersSubscriptionsBundle\Model\Traits\SubscribedUserEntity;
use Vankosoft\PaymentBundle\Model\Interfaces\UserPaymentAwareInterface;
use Vankosoft\PaymentBundle\Model\Traits\UserPaymentAwareEntity;
use Vankosoft\PaymentBundle\Model\Interfaces\CustomerInterface;
use Vankosoft\PaymentBundle\Model\Traits\CustomerEntity;
use Vankosoft\CatalogBundle\Model\Interfaces\UserSubscriptionAwareInterface;
use Vankosoft\CatalogBundle\Model\Traits\UserSubscriptionAwareEntity;
use Vankosoft\UsersBundle\Model\Interfaces\ApiUserInterface;
use Vankosoft\UsersBundle\Model\Traits\ApiUserEntity;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Entity\GamePlayer;
use App\Entity\MercureConnection;

#[ORM\Entity]
#[ORM\Table(name: "VSUM_Users")]
class User extends BaseUser implements
    SubscribedUserInterface,
    UserPaymentAwareInterface,
    CustomerInterface,
    UserSubscriptionAwareInterface,
    ApiUserInterface,
    TwoFactorInterface
{
    use SubscribedUserEntity;
    use UserPaymentAwareEntity;
    use CustomerEntity;
    use UserSubscriptionAwareEntity;
    use ApiUserEntity;
    
    /** @var GamePlayer */
    #[ORM\OneToOne(targetEntity: GamePlayer::class, mappedBy: "user", cascade: ["persist", "remove"], orphanRemoval: true)]
    private $player;
    
    /** @var GamePlayer */
    #[ORM\OneToOne(targetEntity: MercureConnection::class, mappedBy: "user", cascade: ["persist", "remove"], orphanRemoval: true)]
    private $mercureConnection;
    
    /** @var \Datetime */
    #[ORM\Column(name: "last_active_at", type: "datetime", nullable: true)]
    private $lastActiveAt;
    
    /** @var string */
    #[ORM\Column(name: "google_authenticator_secret", type: "string", nullable: true)]
    private $googleAuthenticatorSecret;
    
    public function __construct()
    {
        $this->newsletterSubscriptions  = new ArrayCollection();
        $this->orders                   = new ArrayCollection();
        $this->pricingPlanSubscriptions = new ArrayCollection();
        
        parent::__construct();
    }
    
    /**
     * {@inheritDoc}
     */
    public function getRoles(): array
    {
        /* Use RolesCollection */
        return $this->getRolesFromCollection();
    }
    
    public function getPlayer(): ?GamePlayer
    {
        return $this->player;
    }
    
    public function setPlayer( GamePlayer $player ): self
    {
        $this->player = $player;
        
        return $this;
    }
    
    public function getMercureConnection(): ?MercureConnection
    {
        return $this->mercureConnection;
    }
    
    public function setMercureConnection( MercureConnection $mercureConnection ): self
    {
        $this->mercureConnection = $mercureConnection;
        
        return $this;
    }
    
    public function getLastActiveAt()
    {
        return $this->lastActiveAt;
    }
    
    public function setLastActiveAt( $lastActiveAt )
    {
        $this->lastActiveAt = $lastActiveAt;
    }
    
    public function isGoogleAuthenticatorEnabled(): bool
    {
        return null !== $this->googleAuthenticatorSecret;
    }
    
    public function getGoogleAuthenticatorUsername(): string
    {
        return $this->username;
    }
    
    public function getGoogleAuthenticatorSecret(): ?string
    {
        return $this->googleAuthenticatorSecret;
    }
    
    public function setGoogleAuthenticatorSecret( ?string $googleAuthenticatorSecret ): void
    {
        $this->googleAuthenticatorSecret = $googleAuthenticatorSecret;
    }
}
