<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Illuminate\Support\Str;


class Alert extends Component
{
    
    /**
     * Create a new component instance.
     */
    public function __construct(
        public  $type,
        public  $message
    ) {
        $this->type = $type;
        $this->message = $message;
    }


    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.alert');
    }

    /**
     * コンポーネントをレンダするか
     */
    public function shouldRender(): bool
    {
        return Str::length($this->message) > 0;
    }

}
