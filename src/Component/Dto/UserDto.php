<?php namespace App\Component\Dto;

class UserDto
{        
    public string $id;      
    public string $name;
    public string $email;
    public string $photoUrl;
    public string $socialProvider;
    public string $socialProviderId;
    public bool $createdNew;
}
