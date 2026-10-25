<?php

namespace App\Support;

use InvalidArgumentException;
use OverflowException;

final class Money
{
    public function __construct(public readonly int $minor, public readonly string $currencyCode, public readonly int $minorUnits) {}

    public static function fromMinor(int $minor, string $currencyCode, int $minorUnits): self
    {
        return new self($minor, strtoupper($currencyCode), $minorUnits);
    }

    public static function parse(string $value, string $currencyCode, int $minorUnits): self
    {
        $v = str_replace([',', ' '], '', trim($value));
        $sign = 1;
        if (str_starts_with($v, '-')) {
            $sign = -1;
            $v = substr($v, 1);
        } elseif (str_starts_with($v, '+')) {
            $v = substr($v, 1);
        }
        if (! preg_match('/^\d+(?:\.\d+)?$/', $v)) {
            throw new InvalidArgumentException('Invalid money value.');
        }
        [$whole, $fraction] = array_pad(explode('.', $v, 2), 2, '');
        if (strlen($fraction) > $minorUnits && trim(substr($fraction, $minorUnits), '0') !== '') {
            throw new InvalidArgumentException('Too many decimal places.');
        }
        $fraction = str_pad(substr($fraction, 0, $minorUnits), $minorUnits, '0');
        $factor = self::factor($minorUnits);
        $minor = self::checkedMultiply((int) $whole, $factor) + (int) ($fraction === '' ? 0 : $fraction);

        return new self($sign * $minor, strtoupper($currencyCode), $minorUnits);
    }

    public function toDecimalString(): string
    {
        $negative = $this->minor < 0;
        $n = abs($this->minor);
        if ($this->minorUnits === 0) {
            return ($negative ? '-' : '').$n;
        } $s = str_pad((string) $n, $this->minorUnits + 1, '0', STR_PAD_LEFT);

        return ($negative ? '-' : '').substr($s, 0, -$this->minorUnits).'.'.substr($s, -$this->minorUnits);
    }

    public function format(?string $locale = null): string
    {
        $locale ??= 'en_US';
        if (class_exists('NumberFormatter')) {
            $f = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);
            $f->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $this->minorUnits);
            $f->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $this->minorUnits);

            return $f->format($this->minor / self::factor($this->minorUnits));
        } $decimal = $this->toDecimalString();
        [$a, $b] = array_pad(explode('.', $decimal), 2, '');
        $neg = str_starts_with($a, '-');
        $a = ltrim($a, '-');
        $a = preg_replace('/\B(?=(\d{3})+(?!\d))/', ',', $a);
        $sep = str_starts_with(strtolower($locale), 'de') || str_starts_with(strtolower($locale), 'fr') ? ',' : '.';

        return ($neg ? '-' : '').$a.($this->minorUnits ? $sep.$b : '');
    }

    public function add(self $other): self
    {
        $this->same($other);
        if (($other->minor > 0 && $this->minor > PHP_INT_MAX - $other->minor) || ($other->minor < 0 && $this->minor < PHP_INT_MIN - $other->minor)) {
            throw new OverflowException('Money overflow.');
        }

return new self($this->minor + $other->minor, $this->currencyCode, $this->minorUnits);
    }

    public function subtract(self $other): self
    {
        return $this->add(new self(-$other->minor, $other->currencyCode, $other->minorUnits));
    }

    public function compare(self $other): int
    {
        $this->same($other);

        return $this->minor <=> $other->minor;
    }

    public function multiplyRatio(int $numerator, int $denominator): self
    {
        if ($denominator <= 0) {
            throw new InvalidArgumentException('Denominator must be positive.');
        } $product = self::checkedMultiply($this->minor, $numerator);
        $negative = $product < 0;
        $abs = abs($product);
        $q = intdiv($abs, $denominator);
        $r = $abs % $denominator;
        if ($r >= intdiv($denominator, 2) + ($denominator % 2)) {
            $q++;
        }

return new self($negative ? -$q : $q, $this->currencyCode, $this->minorUnits);
    }

    public function multiplyPercentage(string|int $percent): self
    {
        $p = self::parseDecimal($percent);

        return $this->multiplyRatio($p[0], $p[1] * 100);
    }

    private function same(self $o): void
    {
        if ($o->currencyCode !== $this->currencyCode || $o->minorUnits !== $this->minorUnits) {
            throw new InvalidArgumentException('Money currencies must match.');
        }
    }

    private static function factor(int $u): int
    {
        if ($u < 0 || $u > 3) {
            throw new InvalidArgumentException('Invalid minor units.');
        }

return 10 ** $u;
    }

    private static function checkedMultiply(int $a, int $b): int
    {
        if ($a !== 0 && abs($b) > intdiv(PHP_INT_MAX, abs($a))) {
            throw new OverflowException('Money overflow.');
        }

return $a * $b;
    }

    private static function parseDecimal(string|int $v): array
    {
        $s = (string) $v;
        [$a, $b] = array_pad(explode('.', $s, 2), 2, '');
        $scale = 10 ** strlen($b);

        return [(int) ($a.$b), $scale];
    }
}
