<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class GuestLayout extends Component
{
    public function __construct(
        public string $title = 'Nyuci.id',
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

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.guest', [
            'appName' => $this->appName(),
            'pageTitle' => $this->pageTitle(),
        ]);
    }
}
