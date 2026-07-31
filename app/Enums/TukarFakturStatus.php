<?php

namespace App\Enums;

/**
 * Alur status pengajuan tukar faktur:
 *
 *   pending → email_sent → verified → billing
 *
 * Verifikasi dilakukan SETELAH email bukti terkirim ke supplier, bukan
 * sebelumnya. Tidak ada jalur penolakan.
 */
enum TukarFakturStatus: string
{
    case Pending = 'pending';
    case EmailSent = 'email_sent';
    case Verified = 'verified';
    case Billing = 'billing';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::EmailSent => 'Email Terkirim',
            self::Verified => 'Terverifikasi',
            self::Billing => 'Masuk Billing',
        };
    }

    /** Status berikutnya yang sah, atau null bila sudah di ujung alur. */
    public function next(): ?self
    {
        return match ($this) {
            self::Pending => self::EmailSent,
            self::EmailSent => self::Verified,
            self::Verified => self::Billing,
            self::Billing => null,
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return $this->next() === $target;
    }

    /**
     * Data masih boleh diubah/dihapus selama belum diverifikasi. Setelah
     * verified, angkanya sudah dipakai billing.
     */
    public function isEditable(): bool
    {
        return in_array($this, [self::Pending, self::EmailSent], true);
    }

    /** Sudah lolos verifikasi — dipakai modul billing. */
    public function isVerified(): bool
    {
        return in_array($this, [self::Verified, self::Billing], true);
    }

    /** @return array<string, string> value => label */
    public static function options(): array
    {
        return array_reduce(
            self::cases(),
            fn (array $carry, self $status) => $carry + [$status->value => $status->label()],
            []
        );
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
