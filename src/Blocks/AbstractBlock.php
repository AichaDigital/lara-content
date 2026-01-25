<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Blocks;

use AichaDigital\LaraContent\Blocks\Contracts\BlockContract;
use Illuminate\Contracts\View\View;

/**
 * Abstract base class for blocks.
 *
 * Provides common functionality for all block implementations.
 */
abstract class AbstractBlock implements BlockContract
{
    /**
     * Get the unique slug identifier for this block type.
     */
    abstract public function getSlug(): string;

    /**
     * Get the human-readable name of this block type.
     */
    abstract public function getName(): string;

    /**
     * Get the view name for this block.
     */
    public function getViewName(): string
    {
        return 'content::blocks.'.$this->getSlug();
    }

    /**
     * Get the allowed zones where this block can be placed.
     *
     * @return array<string>
     */
    public function getAllowedZones(): array
    {
        return ['*']; // All zones by default
    }

    /**
     * Get the configuration schema for this block.
     *
     * @return array<string, array{type: string, required?: bool, default?: mixed}>
     */
    public function getConfigSchema(): array
    {
        return [];
    }

    /**
     * Whether this block requires Livewire for interactivity.
     */
    public function isInteractive(): bool
    {
        return false;
    }

    /**
     * Validate the configuration for this block.
     *
     * @param  array<string, mixed>  $config
     * @return array<string, string>
     */
    public function validateConfig(array $config): array
    {
        $errors = [];
        $schema = $this->getConfigSchema();

        foreach ($schema as $key => $rules) {
            $required = $rules['required'] ?? false;

            if ($required && ! isset($config[$key])) {
                $errors[$key] = "The {$key} field is required.";
            }

            if (isset($config[$key]) && isset($rules['type'])) {
                if (! $this->validateType($config[$key], $rules['type'])) {
                    $errors[$key] = "The {$key} field must be of type {$rules['type']}.";
                }
            }
        }

        return $errors;
    }

    /**
     * Render the block with the given context.
     */
    public function render(BlockContext $context): View|string
    {
        return view($this->getViewName(), [
            'context' => $context,
            'config' => $context->config,
            'block' => $this,
        ]);
    }

    /**
     * Get a config value with default from schema.
     */
    protected function getConfigWithDefault(BlockContext $context, string $key): mixed
    {
        if ($context->has($key)) {
            return $context->get($key);
        }

        $schema = $this->getConfigSchema();

        return $schema[$key]['default'] ?? null;
    }

    /**
     * Validate a value against a type.
     */
    protected function validateType(mixed $value, string $type): bool
    {
        return match ($type) {
            'string' => is_string($value),
            'int', 'integer' => is_int($value),
            'float', 'double' => is_float($value) || is_int($value),
            'bool', 'boolean' => is_bool($value),
            'array' => is_array($value),
            default => true,
        };
    }
}
