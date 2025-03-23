<?php

declare(strict_types=1);

namespace PHPAether\Utils\Forms;

class FormValidator
{

    protected array $field;
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
                if (! $validatorFn($value)) {
                    $errorField = $alias ?? $field;
                    $this->errors[$field] = is_string($errorMsg) ? $errorMsg : (is_callable($errorMsg) ? $errorMsg($value, $errorField) : "Invalid {$errorField}");

                    continue 2;
                }
            }
        }

        return $this->errors;
    }

    public function for(string $field, ?string $alias=null): self
    {
        if (! isset($this->form, $field)) {
            throw new \InvalidArgumentException("The field: $field provided is not in the form");
        }

        $this->field = [$field, $alias];
        return $this;
    }

    protected function validateFor(
        callable $validatorFn,
        string|callable|null $errorMsg=null
    ): self
    {
        if (! isset($this->field)) {
            throw new \FormValidatorException("No field is selected for validation. Use the `for` method to select a field");
        }

        // Register the validator callback function
        [$field, $alias] = $this->field;
        $this->validators[$field] ??= [];
        $this->validators[$field][] = [$validatorFn, $errorMsg, $alias]; 

        return $this;
    }

    public function email(
        string|callable|null $errorMsg=null
    ): self
    {
        return $this->validateFor(
            fn (string $value): bool => false, // validate for email here
            $errorMsg
        );

    }

    public function text(
        ?int $min=null,
        ?int $max=null,
        string|callable|null $errorMsg=null
    ): self
    {
        return $this->validateFor(
            fn (string $value): bool => true, //
            $errorMsg
        );
    }

    public function tel(
        string|callable|null $errorMsg=null
    ): self
    {
        return $this->validateFor(
            fn (string $value): bool => (
                is_numeric($value)
            ),
            $errorMsg
        );
    }

    public function url(
        string|callable|null $errorMsg=null
    ): self
    {
        return $this->validateFor(
            fn (string $value): bool => false,
            $errorMsg
        );
    }

    public function regex(
        string $pattern,
        string|callable|null $errorMsg=null
    ): self
    {
        return $this->validateFor(
            fn (string $value): bool => (bool) preg_match($pattern, $value),
            $errorMsg
        );
    }

    public function password(
        ?int $min=8,
        ?int $max=15,
        ?int $options=null,
        string|callable|null $errorMsg=null
    ): self
    {
        return $this->validateFor(
            fn (string $value): bool => false,
            $errorMsg
        );
    }

    public function matches(
        string $value,
        ?string $regex=null,
        string|callable|null $errorMsg=null
    ): self
    {
        return $this->validateFor(
            fn (string $value): bool  => false,
            $errorMsg
        );
    }

}
