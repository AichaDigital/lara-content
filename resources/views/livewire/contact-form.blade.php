<div class="contact-form">
    @if($submitted)
        <div class="contact-form__success">
            <p>{{ $successMessage }}</p>
            <button type="button" wire:click="resetForm" class="contact-form__reset-btn">
                {{ __('content::blocks.contact_form.send_another') }}
            </button>
        </div>
    @else
        <form wire:submit="submit" class="contact-form__form">
            @if($errors->has('form'))
                <div class="contact-form__error">
                    {{ $errors->first('form') }}
                </div>
            @endif

            <div class="contact-form__field">
                <label for="name" class="contact-form__label">
                    {{ __('content::blocks.contact_form.name') }} *
                </label>
                <input
                    type="text"
                    id="name"
                    wire:model="name"
                    class="contact-form__input @error('name') contact-form__input--error @enderror"
                    required
                >
                @error('name')
                    <span class="contact-form__field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="contact-form__field">
                <label for="email" class="contact-form__label">
                    {{ __('content::blocks.contact_form.email') }} *
                </label>
                <input
                    type="email"
                    id="email"
                    wire:model="email"
                    class="contact-form__input @error('email') contact-form__input--error @enderror"
                    required
                >
                @error('email')
                    <span class="contact-form__field-error">{{ $message }}</span>
                @enderror
            </div>

            @if($showPhone)
                <div class="contact-form__field">
                    <label for="phone" class="contact-form__label">
                        {{ __('content::blocks.contact_form.phone') }}
                    </label>
                    <input
                        type="tel"
                        id="phone"
                        wire:model="phone"
                        class="contact-form__input @error('phone') contact-form__input--error @enderror"
                    >
                    @error('phone')
                        <span class="contact-form__field-error">{{ $message }}</span>
                    @enderror
                </div>
            @endif

            @if($showSubject)
                <div class="contact-form__field">
                    <label for="subject" class="contact-form__label">
                        {{ __('content::blocks.contact_form.subject') }}
                    </label>
                    <input
                        type="text"
                        id="subject"
                        wire:model="subject"
                        class="contact-form__input @error('subject') contact-form__input--error @enderror"
                    >
                    @error('subject')
                        <span class="contact-form__field-error">{{ $message }}</span>
                    @enderror
                </div>
            @endif

            <div class="contact-form__field">
                <label for="message" class="contact-form__label">
                    {{ __('content::blocks.contact_form.message') }} *
                </label>
                <textarea
                    id="message"
                    wire:model="message"
                    rows="5"
                    class="contact-form__textarea @error('message') contact-form__textarea--error @enderror"
                    required
                ></textarea>
                @error('message')
                    <span class="contact-form__field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="contact-form__actions">
                <button type="submit" class="contact-form__submit" wire:loading.attr="disabled">
                    <span wire:loading.remove>{{ __('content::blocks.contact_form.submit') }}</span>
                    <span wire:loading>{{ __('content::blocks.contact_form.sending') }}</span>
                </button>
            </div>
        </form>
    @endif
</div>
