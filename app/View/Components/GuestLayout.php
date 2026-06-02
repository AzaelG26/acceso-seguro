<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class GuestLayout extends Component
{
    /**
     * Obtiene la vista o contenido que representa el componente.
     */
    public function render(): View
    {
        return view('layouts.guest');
    }
}
