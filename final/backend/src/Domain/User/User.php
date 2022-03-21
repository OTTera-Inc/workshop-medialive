<?php

declare(strict_types=1);

namespace App\Domain\Channel;

use JsonSerializable;

class Channel implements JsonSerializable
{
    private ?int $id;

    private string $channelname;

    private string $firstName;

    private string $lastName;

    public function __construct(?int $id, string $channelname, string $firstName, string $lastName)
    {
        $this->id = $id;
        $this->channelname = strtolower($channelname);
        $this->firstName = ucfirst($firstName);
        $this->lastName = ucfirst($lastName);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChannelname(): string
    {
        return $this->channelname;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'channelname' => $this->channelname,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
        ];
    }
}
