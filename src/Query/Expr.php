<?php
/*
 * The Eufony DBAL Package
 * Copyright (c) 2021 Alpin Gencer
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace Eufony\DBAL\Query;

use Eufony\DBAL\Query\Builder\Select;

class Expr
{
    protected string $type;
    protected array $props;
    protected array $context;

    public static function true(): static
    {
        return new static(__FUNCTION__);
    }

    public static function false(): static
    {
        return Expr::not(Expr::true());
    }

    public static function not(Expr $expr): static
    {
        return new static(__FUNCTION__, ["expr" => $expr]);
    }

    public static function and(Expr ...$expr): static
    {
        return new static(__FUNCTION__, ["expr" => $expr]);
    }

    public static function or(Expr ...$expr): static
    {
        return new static(__FUNCTION__, ["expr" => $expr]);
    }

    public static function nand(Expr ...$expr): static
    {
        return Expr::not(Expr::and(...$expr));
    }

    public static function nor(Expr ...$expr): static
    {
        return Expr::not(Expr::or(...$expr));
    }

    public static function xor(Expr ...$expr): static
    {
        return Expr::and(Expr::or(...$expr), Expr::not(Expr::and(...$expr)));
    }

    public static function xnor(Expr ...$expr): static
    {
        return Expr::not(Expr::xor(...$expr));
    }

    public static function same(string $primary, string $foreign): static
    {
        return new static(__FUNCTION__, ["primary" => $primary, "foreign" => $foreign]);
    }

    public static function lt(string $field, int|float|string $value): static
    {
        return new static(__FUNCTION__, ["field" => $field, "value" => $value]);
    }

    public static function le(string $field, int|float|string $value): static
    {
        return new static(__FUNCTION__, ["field" => $field, "value" => $value]);
    }

    public static function ge(string $field, int|float|string $value): static
    {
        return new static(__FUNCTION__, ["field" => $field, "value" => $value]);
    }

    public static function gt(string $field, int|float|string $value): static
    {
        return new static(__FUNCTION__, ["field" => $field, "value" => $value]);
    }

    public static function eq(string $field, int|float|string|bool|null $value): static
    {
        return new static(__FUNCTION__, ["field" => $field, "value" => $value]);
    }

    public static function ne(string $field, int|float|string|bool|null $value): static
    {
        return new static(__FUNCTION__, ["field" => $field, "value" => $value]);
    }

    public static function like(string $field, string $value): static
    {
        return new static(__FUNCTION__, ["field" => $field, "value" => $value]);
    }

    public static function in(string $field, array $values): static
    {
        return new static(__FUNCTION__, ["field" => $field, "values" => $values]);
    }

    public static function exists(Select $query): static
    {
        return new static(__FUNCTION__, ["query" => $query]);
    }

    protected function __construct(string $type, array $props = [])
    {
        $this->type = $type;
        $this->props = $props;
        $this->context = [];

        switch ($type) {
            case "lt":
            case "le":
            case "ge":
            case "gt":
                if (is_string($this->props['value'])) {
                    break;
                }
            case "eq":
            case "ne":
            case "like":
                $value = &$this->props['value'];
                $placeholder = hash("md5", uniqid(more_entropy: true));
                $this->context[$placeholder] = $value;
                $value = ":" . $placeholder;
                break;
            case "in":
                foreach ($this->props['values'] as &$value) {
                    $placeholder = hash("md5", uniqid(more_entropy: true));
                    $this->context[$placeholder] = $value;
                    $value = ":" . $placeholder;
                }
                break;
            default:
                break;
        }
    }

    public function type(): string
    {
        return $this->type;
    }

    public function props(): array
    {
        return $this->props;
    }

    public function context(bool $recursive = false): array
    {
        if (!$recursive) {
            return $this->context;
        }

        return match ($this->type) {
            "not" => array_merge($this->context, $this->props['expr']->context),
            "and", "or" => array_merge($this->context, ...array_map(fn($expr) => $expr->context, $this->props['expr'])),
            default => $this->context,
        };
    }
}
