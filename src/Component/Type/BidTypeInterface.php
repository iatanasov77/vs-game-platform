<?php namespace App\Component\Type;

interface BidTypeInterface
{
    public function color(): string;
    public function value(): int;
    public function bitMaskValue(): int;
    public static function fromValue( int $value ): self;
    public static function fromBitMaskValue( int $value ): self;
}
