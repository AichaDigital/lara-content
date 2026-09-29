<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Blocks;

use Illuminate\Contracts\View\View;
use Livewire\Livewire;

/**
 * Contact form block (interactive).
 *
 * Renders a Livewire contact form component.
 */
class ContactFormBlock extends AbstractBlock
{
    public function getSlug(): string
    {
        return 'contact-form';
    }

    public function getName(): string
    {
        return __('content::blocks.contact_form.name');
    }

    /**
     * @return array<string>
     */
    public function getAllowedZones(): array
    {
        return ['main'];
    }

    public function isInteractive(): bool
    {
        return true;
    }

    /**
     * @return array<string, array{type: string, required?: bool, default?: mixed}>
     */
    public function getConfigSchema(): array
    {
        return [
            'recipient_email' => [
                'type' => 'string',
                'required' => true,
                'default' => '',
            ],
            'subject_prefix' => [
                'type' => 'string',
                'required' => false,
                'default' => '[Contact Form]',
            ],
            'success_message' => [
                'type' => 'string',
                'required' => false,
                'default' => '',
            ],
            'show_phone' => [
                'type' => 'boolean',
                'required' => false,
                'default' => false,
            ],
            'show_subject' => [
                'type' => 'boolean',
                'required' => false,
                'default' => true,
            ],
        ];
    }

    public function render(BlockContext $context): View|string
    {
        // Check if Livewire is available
        if (! class_exists(Livewire::class)) {
            /** @var view-string $fallbackView */
            $fallbackView = 'content::blocks.contact-form-fallback';

            return view($fallbackView, [
                'context' => $context,
                'block' => $this,
            ]);
        }

        /** @var view-string $viewName */
        $viewName = $this->getViewName();

        return view($viewName, [
            'context' => $context,
            'config' => $context->config,
            'block' => $this,
        ]);
    }
}
