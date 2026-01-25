<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Blocks\Contracts;

use AichaDigital\LaraContent\Blocks\BlockContext;
use Illuminate\Contracts\View\View;

/**
 * Contract for content blocks.
 *
 * Blocks are the content units that can be placed within layout zones.
 */
interface BlockContract
{
    /**
     * Get the unique slug identifier for this block type.
     */
    public function getSlug(): string;

    /**
     * Get the human-readable name of this block type.
     */
    public function getName(): string;

    /**
     * Get the allowed zones where this block can be placed.
     *
     * @return array<string> Zone identifiers, or ['*'] for all zones
     */
    public function getAllowedZones(): array;

    /**
     * Get the configuration schema for this block.
     *
     * Returns an array describing the expected configuration fields.
     *
     * @return array<string, array{type: string, required?: bool, default?: mixed}>
     */
    public function getConfigSchema(): array;

    /**
     * Render the block with the given context.
     *
     * @param  BlockContext  $context  The rendering context including config and page data
     */
    public function render(BlockContext $context): View|string;

    /**
     * Whether this block requires Livewire for interactivity.
     */
    public function isInteractive(): bool;

    /**
     * Validate the configuration for this block.
     *
     * @param  array<string, mixed>  $config
     * @return array<string, string> Validation errors, empty if valid
     */
    public function validateConfig(array $config): array;
}
