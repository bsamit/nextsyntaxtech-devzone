<?php

namespace NextSyntaxTech\DevZone\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Alert extends Component
{
    public string $type;
    public ?string $title;
    public ?string $message;
    public ?string $icon;
    public bool $dismissible;
    public bool $session;

    /**
     * Create a new Alert component instance.
     *
     * @param string $type
     * @param string|null $title
     * @param string|null $message
     * @param string|null $icon
     * @param bool $dismissible
     * @param bool $session
     */
    public function __construct(
        string $type = 'info',
        ?string $title = null,
        ?string $message = null,
        ?string $icon = null,
        bool $dismissible = true,
        bool $session = false
    ) {
        $this->type = strtolower($type) === 'error' ? 'danger' : strtolower($type);
        $this->title = $title;
        $this->message = $message;
        $this->icon = $icon;
        $this->dismissible = $dismissible;
        $this->session = $session;
    }

    /**
     * Get the default Boxicon based on the alert type.
     */
    public function iconClass(): string
    {
        if (!empty($this->icon)) {
            return $this->icon;
        }

        return match ($this->type) {
            'success'           => 'bx bx-check-circle',
            'danger'            => 'bx bx-error-circle',
            'warning'           => 'bx bx-alarm-exclamation',
            'info'              => 'bx bx-info-circle',
            'primary'           => 'bx bx-bell',
            default             => 'bx bx-info-circle',
        };
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('devzone::components.alert');
    }
}
