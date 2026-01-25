<?php

declare(strict_types=1);

namespace AichaDigital\LaraContent\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * Livewire contact form component.
 */
class ContactForm extends Component
{
    #[Validate('required|string|max:100')]
    public string $name = '';

    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('nullable|string|max:20')]
    public string $phone = '';

    #[Validate('nullable|string|max:200')]
    public string $subject = '';

    #[Validate('required|string|min:10|max:5000')]
    public string $message = '';

    public string $recipientEmail = '';

    public string $subjectPrefix = '[Contact Form]';

    public string $successMessage = '';

    public bool $showPhone = false;

    public bool $showSubject = true;

    public bool $submitted = false;

    /**
     * Mount the component with configuration.
     *
     * @param  array<string, mixed>  $config
     */
    public function mount(array $config = []): void
    {
        $this->recipientEmail = $config['recipient_email'] ?? '';
        $this->subjectPrefix = $config['subject_prefix'] ?? '[Contact Form]';
        $this->successMessage = $config['success_message'] ?? __('content::blocks.contact_form.success');
        $this->showPhone = $config['show_phone'] ?? false;
        $this->showSubject = $config['show_subject'] ?? true;
    }

    /**
     * Submit the contact form.
     */
    public function submit(): void
    {
        $this->validate();

        if (empty($this->recipientEmail)) {
            $this->addError('form', __('content::blocks.contact_form.no_recipient'));

            return;
        }

        try {
            $this->sendEmail();
            $this->submitted = true;
            $this->reset(['name', 'email', 'phone', 'subject', 'message']);
        } catch (\Exception $e) {
            $this->addError('form', __('content::blocks.contact_form.error'));
            report($e);
        }
    }

    /**
     * Send the contact email.
     */
    protected function sendEmail(): void
    {
        $emailSubject = $this->subjectPrefix.' '.($this->subject ?: __('content::blocks.contact_form.default_subject'));

        Mail::raw($this->buildEmailBody(), function ($message) use ($emailSubject): void {
            $message->to($this->recipientEmail)
                ->replyTo($this->email, $this->name)
                ->subject($emailSubject);
        });
    }

    /**
     * Build the email body.
     */
    protected function buildEmailBody(): string
    {
        $body = __('content::blocks.contact_form.email_from').": {$this->name}\n";
        $body .= __('content::blocks.contact_form.email_email').": {$this->email}\n";

        if ($this->showPhone && $this->phone) {
            $body .= __('content::blocks.contact_form.email_phone').": {$this->phone}\n";
        }

        $body .= "\n".__('content::blocks.contact_form.email_message').":\n";
        $body .= $this->message;

        return $body;
    }

    /**
     * Reset the form to initial state.
     */
    public function resetForm(): void
    {
        $this->submitted = false;
        $this->resetValidation();
    }

    public function render(): View
    {
        return view('content::livewire.contact-form');
    }
}
