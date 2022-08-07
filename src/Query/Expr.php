<?php
/*
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

/**
 * Represents an SQL expression, such as a comparison operation, using polish
 * notation.
 *
 * @see https://en.wikipedia.org/wiki/Polish_notation
 */
class Expr
{
    /**
     * The type of the expression that corresponds to one of the static functions
     * used to initialize an instance of this class.
     *
     * @var string $type
     */
    protected string $type;

    /**
     * Additional properties depending on the type of the expression.
     *
     * @var mixed[] $props
     */
    protected array $props;

    /**
     * An array of key-value pairs that map placeholder.
     *
     * @var mixed[] $context
     */
    protected array $context;

    /**
     * Initializes a new expression instance.
     *
     * The expression always evaluates to be true.
     *
     * @return static
     */
    public static function true(): static
    {
        return new static(__FUNCTION__);
    }

    /**
     * Initializes a new expression instance.
     *
     * The expression always evaluates to be false.
     *
     * @return static
     */
    public static function false(): static
    {
        return Expr::not(Expr::true());
    }

    /**
     * Initializes a new expression instance.
     *
     * Negates the given expression.
     *
     * @param Expr $expr
     * @return static
     */
    public static function not(Expr $expr): static
    {
        return new static(__FUNCTION__, ["expr" => $expr]);
    }

    /**
     * Initializes a new expression instance.
     *
     * Evaluates to be true if all of the given expressions match.
     *
     * @param Expr[] $expr
     * @return static
     */
    public static function and(Expr ...$expr): static
    {
        return new static(__FUNCTION__, ["expr" => $expr]);
    }

    /**
     * Initializes a new expression instance.
     *
     * Evaluates to be true if any of the given expressions match.
     *
     * @param Expr[] $expr
     * @return static
     */
    public static function or(Expr ...$expr): static
    {
        return new static(__FUNCTION__, ["expr" => $expr]);
    }

    /**
     * Initializes a new expression instance.
     *
     * Evaluates to be true if not all of the given expressions match.
     *
     * @param Expr[] $expr
     * @return static
     */
    public static function nand(Expr ...$expr): static
    {
        return Expr::not(Expr::and(...$expr));
    }

    /**
     * Initializes a new expression instance.
     *
     * Evaluates to be true if none of the given expressions match.
     *
     * @param Expr[] $expr
     * @return static
     */
    public static function nor(Expr ...$expr): static
    {
        return Expr::not(Expr::or(...$expr));
    }

    /**
     * Initializes a new expression instance.
     *
     * Evaluates to be true if any, but not all, of the given expressions match.
     *
     * @param Expr[] $expr
     * @return static
     */
    public static function xor(Expr ...$expr): static
    {
        return Expr::and(Expr::or(...$expr), Expr::not(Expr::and(...$expr)));
    }

    /**
     * Initializes a new expression instance.
     *
     * Evaluates to be true if either none or all of the given expressions match.
     *
     * @param Expr[] $expr
     * @return static
     */
    public static function xnor(Expr ...$expr): static
    {
        return Expr::not(Expr::xor(...$expr));
    }

    /**
     * Initializes a new expression instance.
     *
     * Evaluates to be true if the values of the two given fields are equal.
     *
     * @param string $primary
     * @param string $foreign
     * @return static
     */
    public static function same(string $primary, string $foreign): static
    {
        return new static(__FUNCTION__, ["primary" => $primary, "foreign" => $foreign]);
    }

    /**
     * Initializes a new expression instance.
     *
     * Evaluates to be true if the value of the given field is less than the given
     * numeric value.
     * If `$value` is a string, the value is assumed to be another field name.
     *
     * @param string $field
     * @param int|float|string $value
     * @return static
     */
    public static function lt(string $field, int|float|string $value): static
    {
        return new static(__FUNCTION__, ["field" => $field, "value" => $value]);
    }

    /**
     * Initializes a new expression instance.
     *
     * Evaluates to be true if the value of the given field is less than or equal
     * to the given numeric value.
     * If `$value` is a string, the value is assumed to be another field name.
     *
     * @param string $field
     * @param int|float|string $value
     * @return static
     */
    public static function le(string $field, int|float|string $value): static
    {
        return new static(__FUNCTION__, ["field" => $field, "value" => $value]);
    }

    /**
     * Initializes a new expression instance.
     *
     * Evaluates to be true if the value of the given field is greater than or
     * equal to the given numeric value.
     * If `$value` is a string, the value is assumed to be another field name.
     *
     * @param string $field
     * @param int|float|string $value
     * @return static
     */
    public static function ge(string $field, int|float|string $value): static
    {
        return new static(__FUNCTION__, ["field" => $field, "value" => $value]);
    }

    /**
     * Initializes a new expression instance.
     *
     * Evaluates to be true if the value of the given field is greater than the
     * given numeric value.
     * If `$value` is a string, the value is assumed to be another field name.
     *
     * @param string $field
     * @param int|float|string $value
     * @return static
     */
    public static function gt(string $field, int|float|string $value): static
    {
        return new static(__FUNCTION__, ["field" => $field, "value" => $value]);
    }

    /**
     * Initializes a new expression instance.
     *
     * Evaluates to be true if the value of the given field is equal to the given
     * value.
     *
     * @param string $field
     * @param int|float|string|bool|null $value
     * @return static
     */
    public static function eq(string $field, int|float|string|bool|null $value): static
    {
        return new static(__FUNCTION__, ["field" => $field, "value" => $value]);
    }

    /**
     * Initializes a new expression instance.
     *
     * Evaluates to be true if the value of the given field is not equal to the
     * given value.
     *
     * @param string $field
     * @param int|float|string|bool|null $value
     * @return static
     */
    public static function ne(string $field, int|float|string|bool|null $value): static
    {
        return new static(__FUNCTION__, ["field" => $field, "value" => $value]);
    }

    /**
     * Initializes a new expression instance.
     *
     * Evaluates to be true if the value of the given field matches the given
     * value.
     * Percent signs (`%`) in the value are accepted to be wildcard characters.
     *
     * @param string $field
     * @param string $value
     * @return static
     */
    public static function like(string $field, string $value): static
    {
        return new static(__FUNCTION__, ["field" => $field, "value" => $value]);
    }

    /**
     * Initializes a new expression instance.
     *
     * Evaluates to be true if the value of the given field is equal to one of the
     * values in the given array.
     *
     * @param string $field
     * @param array $values
     * @return static
     */
    public static function in(string $field, array $values): static
    {
        return new static(__FUNCTION__, ["field" => $field, "values" => $values]);
    }

    /**
     * Initializes a new expression instance.
     *
     * Evaluates to be true if the given `Select` sub-query returns at least one
     * row.
     *
     * @param Select $query
     * @return static
     */
    public static function exists(Select $query): static
    {
        return new static(__FUNCTION__, ["query" => $query]);
    }

    /**
     * Private class constructor.
     * An expression cannot be initialized using the `new` syntax.
     *
     * Use one of the static functions to initialize an instance of this class.
     *
     * @param string $type
     * @param array $props
     */
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

    /**
     * Returns the expression type.
     *
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * Returns the expression properties.
     *
     * @return mixed[]
     */
    public function props(): array
    {
        return $this->props;
    }

    /**
     * Returns the expression context.
     *
     * Optionally recurses into any sub-expressions this expression might have.
     *
     * @param bool $recursive
     * @return mixed[]
     */
    public function context(bool $recursive = false): array
    {
        if (!$recursive) {
            return $this->context;
        }

        return match ($this->type) {
            "not" => [...$this->context, ...$this->props['expr']->context],
            "and", "or" => array_merge($this->context, ...array_map(fn($expr) => $expr->context, $this->props['expr'])),
            default => $this->context,
        };
    }

    /**
     * Returns a list of tables that are referenced by this expression.
     *
     * @return string[]
     */
    public function affectedTables(): array
    {
        // TODO: Additional tables might be referenced in field names.
        return $this->type === "exists" ? $this->props['query']->affectedTables() : [];
    }
}
