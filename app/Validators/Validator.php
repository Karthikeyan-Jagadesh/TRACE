<?php
namespace App\Validators;

class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];

    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->validate();
    }

    public static function make(array $data, array $rules): self
    {
        return new self($data, $rules);
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function first(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    private function validate(): void
    {
        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }

                $rule = trim($rule);

                if ($rule === 'required' && (is_null($value) || trim((string)$value) === '')) {
                    $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' is required.');
                    break; // stop evaluating further rules for this field if empty
                }

                // If not required and value is empty, skip remaining checks
                if (is_null($value) || trim((string)$value) === '') {
                    continue;
                }

                if ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, 'Please enter a valid email address.');
                }

                if ($rule === 'min') {
                    $min = (int)($params[0] ?? 0);
                    if (mb_strlen((string)$value) < $min) {
                        $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . " must be at least {$min} characters.");
                    }
                }

                if ($rule === 'max') {
                    $max = (int)($params[0] ?? 0);
                    if (mb_strlen((string)$value) > $max) {
                        $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . " must not exceed {$max} characters.");
                    }
                }

                if ($rule === 'in') {
                    if (!in_array($value, $params, true)) {
                        $this->addError($field, 'Selected value is invalid.');
                    }
                }

                if ($rule === 'numeric' && !is_numeric($value)) {
                    $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' must be a number.');
                }

                if ($rule === 'date') {
                    $d = \DateTime::createFromFormat('Y-m-d', $value);
                    if (!$d || $d->format('Y-m-d') !== $value) {
                        $this->addError($field, 'Please provide a valid date in YYYY-MM-DD format.');
                    }
                }

                if ($rule === 'matches') {
                    $otherField = $params[0] ?? '';
                    $otherValue = $this->data[$otherField] ?? null;
                    if ($value !== $otherValue) {
                        $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' does not match.');
                    }
                }
            }
        }
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }
}
