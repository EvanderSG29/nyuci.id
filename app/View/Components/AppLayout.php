<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    public function __construct(
        public string $title = 'Nyuci.id',
        public ?string $navbarEyebrow = null,
    ) {
    }

    protected function appName(): string
    {
        $appName = trim((string) config('app.name', ''));

        return $appName !== '' && $appName !== 'Laravel' ? $appName : 'Nyuci.id';
    }

    protected function pageTitle(): string
    {
        $title = trim($this->title);

        return $title !== '' ? $title : $this->appName();
    }

    protected function pageNavbarEyebrow(): string
    {
        $eyebrow = trim((string) ($this->navbarEyebrow ?? ''));

        return $eyebrow;
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app', [
            'appName' => $this->appName(),
            'pageTitle' => $this->pageTitle(),
            'pageNavbarEyebrow' => $this->pageNavbarEyebrow(),
        ]);
    }
}
