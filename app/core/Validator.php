<?php
/**
 * PropIntel CRM - Input Validator
 */

class Validator
{
    private array $errors = [];
    private array $data   = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /** Static factory */
    public static function make(array $data, array $rules): self
    {
        $v = new self($data);
        $v->validate($rules);
        return $v;
    }

    public function validate(array $rules): void
    {
        foreach ($rules as $field => $ruleStr) {
            $ruleParts = explode('|', $ruleStr);
            $value     = $this->data[$field] ?? null;
            $label     = ucwords(str_replace('_', ' ', $field));

            foreach ($ruleParts as $rule) {
                [$ruleName, $ruleParam] = array_pad(explode(':', $rule, 2), 2, null);

                switch ($ruleName) {
                    case 'required':
                        if ($value === null || $value === '') {
                            $this->errors[$field][] = "{$label} is required.";
                        }
                        break;

                    case 'email':
                        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $this->errors[$field][] = "{$label} must be a valid email address.";
                        }
                        break;

                    case 'min':
                        if (strlen((string)$value) < (int)$ruleParam) {
                            $this->errors[$field][] = "{$label} must be at least {$ruleParam} characters.";
                        }
                        break;

                    case 'max':
                        if (strlen((string)$value) > (int)$ruleParam) {
                            $this->errors[$field][] = "{$label} must not exceed {$ruleParam} characters.";
                        }
                        break;

                    case 'numeric':
                        if ($value !== null && $value !== '' && !is_numeric($value)) {
                            $this->errors[$field][] = "{$label} must be a number.";
                        }
                        break;

                    case 'integer':
                        if ($value !== null && $value !== '' && filter_var($value, FILTER_VALIDATE_INT) === false) {
                            $this->errors[$field][] = "{$label} must be an integer.";
                        }
                        break;

                    case 'in':
                        $allowed = explode(',', $ruleParam ?? '');
                        if ($value !== null && $value !== '' && !in_array($value, $allowed, true)) {
                            $this->errors[$field][] = "{$label} has an invalid value.";
                        }
                        break;

                    case 'date':
                        if ($value !== null && $value !== '') {
                            $d = DateTime::createFromFormat('Y-m-d', $value);
                            if (!$d || $d->format('Y-m-d') !== $value) {
                                $this->errors[$field][] = "{$label} must be a valid date (YYYY-MM-DD).";
                            }
                        }
                        break;

                    case 'url':
                        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
                            $this->errors[$field][] = "{$label} must be a valid URL.";
                        }
                        break;

                    case 'phone':
                        if ($value !== null && $value !== '' && !preg_match('/^\+?[\d\s\-().]{7,20}$/', $value)) {
                            $this->errors[$field][] = "{$label} must be a valid phone number.";
                        }
                        break;
                }
            }
        }
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(string $field): string
    {
        return $this->errors[$field][0] ?? '';
    }

    /** Return sanitized value */
    public function get(string $field, mixed $default = null): mixed
    {
        return $this->data[$field] ?? $default;
    }

    /** Return all validated data (trimmed strings) */
    public function all(): array
    {
        return array_map(function ($v) {
            return is_string($v) ? trim($v) : $v;
        }, $this->data);
    }
}
