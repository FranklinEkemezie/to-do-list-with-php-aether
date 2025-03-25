<?php

declare(strict_types=1);

namespace PHPAether\Utils\Forms;

use PHPAether\Exceptions\FormValidatorException;

class FormValidator
{

    protected array $currentFields;
    protected array $validators = [];
    protected array $errors = [];

    public function __construct(
        protected array $form
    ) {
        
    }

    public function validate(?callable $validatorCallbackFn=null): array
    {

        if ($validatorCallbackFn !== null) {
            $validatorCallbackFn($this);
        }

        foreach ($this->validators as $field => $fieldValidators) {
            foreach ($fieldValidators as $validator) {
                [$validatorFn, $errorMsg, $alias] = $validator;

                $value = $this->form[$field];
                if (! $validatorFn(trim($value))) {
                    $errorField = $alias ?? $field;
                    $this->errors[$field] = is_string($errorMsg) ? $errorMsg : (
                        is_callable($errorMsg) ? $errorMsg($value, $errorField) : "Invalid {$errorField}"
                    );

                    continue 2;
                }
            }
        }

        return $this->errors;
    }

    public function for(string|array ...$fields): self
    {
        foreach ($fields as $field) {
            $field = is_array($field) ?: [$field, null];
            [$field, $alias] = $field;

            if (! isset($this->form, $field)) {
                throw new \InvalidArgumentException("The field: $field provided is not in the form");
            }

            $this->currentFields ??= [];
            $this->currentFields[] = [$field, $alias];
        }

        return $this;
    }

    /**
     * @throws FormValidatorException
     */
    protected function validateFor(
        callable $validatorFn,
        string|callable|null $errorMsg=null
    ): self
    {
        if (! isset($this->currentFields)) {
            throw new FormValidatorException("No field is selected for validation. Use the `for` method to select a field");
        }

        // Register the validator callback function
        foreach ($this->currentFields as $currentField) {
            [$field, $alias] = $currentField;

            $this->validators[$field] ??= [];
            $this->validators[$field][] = [$validatorFn, $errorMsg, $alias];
        }

        return $this;
    }

    /**
     * @throws FormValidatorException
     */
    public function email(
        string|callable|null $errorMsg=null
    ): self
    {
        return $this->validateFor(
            fn (string $value): bool => (
                strlen($value) <= 254 &&
                filter_var($value, FILTER_VALIDATE_EMAIL) !== false
            ),
            $errorMsg
        );

    }

    /**
     * @throws FormValidatorException
     */
    public function text(
        ?int $min=null,
        ?int $max=null,
        string|callable|null $errorMsg=null
    ): self
    {
        return $this->validateFor(
            fn (string $value): bool => (
                (! empty($value) || $min === null) &&
                ($min === null || strlen($value) >= $min) &&
                ($max === null || strlen($value) <= $max)
            ),
            $errorMsg
        );
    }

    /**
     * @throws FormValidatorException
     */
    public function tel(
        string|callable|null $errorMsg=null
    ): self
    {
        return $this->validateFor(
            fn (string $value): bool => (
                preg_match('/^\+?[0-9\s\-()]{7,15}$/', $value) === 1
            ),
            $errorMsg
        );
    }

    /**
     * @throws FormValidatorException
     */
    public function url(
        string|callable|null $errorMsg=null
    ): self
    {
        return $this->validateFor(
            fn (string $value): bool => (
                filter_var($value, FILTER_VALIDATE_URL) !== false
            ),
            $errorMsg
        );
    }

    /**
     * @throws FormValidatorException
     */
    public function regex(
        string $pattern,
        string|callable|null $errorMsg=null
    ): self
    {
        return $this->validateFor(
            fn (string $value): bool => preg_match($pattern, $value) === 1,
            $errorMsg
        );
    }

    /**
     * @throws FormValidatorException
     */
    public function password(
        ?int $min=8,
        ?int $max=15,
        ?int $options=null,
        string|callable|null $errorMsg=null
    ): self
    {
        return $this->validateFor(
            fn (string $value): bool => (
                preg_match(
                    sprintf('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{%d,%d}$/', $min, $max),
                    $value
                ) === 1
            ),
            $errorMsg
        );
    }

    /**
     * @throws FormValidatorException
     */
    public function matches(
        string $field,
        string|callable|null $errorMsg=null
    ): self
    {
        $valueToMatch = $this->form[$field] ?? null;
        if ($valueToMatch === null) {
            throw new FormValidatorException("The form to validate does not contain field: $field");
        }

        return $this->validateFor(
            fn (string $value): bool  => $value === $valueToMatch,
            $errorMsg
        );
    }

    public function date(
        ?string $format='Y-m-d',
        ?string $before=null,
        ?string $after=null,
        ?int $minAge = null,
        ?int $maxAge = null,
        string|callable|null $errorMsg=null
    ): self
    {
        return $this->validateFor(
            function (string $value) use ($format, $before, $after, $minAge, $maxAge): bool {
                $date = \DateTime::createFromFormat($format, $value);
                if (! $date) return false;
                if ($date->format($format) !== $value) return false;

                $timestamp = $date->getTimestamp();
                $now = time();

                if ($before && $timestamp >= strtotime($before)) return false;
                if ($after && $timestamp <= strtotime($after)) return false;

                if ($minAge !== null) {
                    $minTimestamp = strtotime("-{$minAge} years", $now);
                    if ($timestamp > $minTimestamp) return false;
                }

                if ($maxAge !== null) {
                    $maxTimestamp = strtotime("-{$maxAge} years", $now);
                    if ($timestamp < $maxTimestamp) return false;
                }

                return true;
            },
            $errorMsg
        );
    }

}
