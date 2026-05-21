<?php

declare(strict_types=1);

namespace LaravelMailbox\Support;

use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class AddressNormalizer
{
    /**
     * @param  array<int, Address>|null  $addresses
     * @return array<int, array{address: string, name: ?string}>
     */
    public static function fromEmailAddresses(?array $addresses): array
    {
        if ($addresses === null) {
            return [];
        }

        return array_map(
            static fn (Address $address): array => [
                'address' => $address->getAddress(),
                'name' => $address->getName() ?: null,
            ],
            $addresses,
        );
    }

    /**
     * @return array<string, string>
     */
    public static function headersFromEmail(Email $email): array
    {
        $headers = [];

        foreach ($email->getHeaders()->all() as $header) {
            $headers[$header->getName()] = $header->getBodyAsString();
        }

        return $headers;
    }

    /**
     * @param  array<int, array{address: string, name: ?string}>|null  $addresses
     */
    public static function formatAddressList(?array $addresses): string
    {
        if ($addresses === null || $addresses === []) {
            return '';
        }

        return collect($addresses)
            ->map(static function (array $entry): string {
                $name = $entry['name'] ?? null;
                $address = $entry['address'];

                return $name ? sprintf('%s <%s>', $name, $address) : $address;
            })
            ->implode(', ');
    }
}
