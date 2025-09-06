<?php
namespace App\View\Components;

use Illuminate\View\Component;

class DashboardCard extends Component
{
    public $title;
    public $icon;
    public $value;
    public $color;

    public function __construct($title, $icon, $value, $color = 'primary')
    {
        $this->title = $title;
        $this->icon = $icon;
        $this->value = $value;
        $this->color = $color;
    }

    public function render()
    {
        return view('components.dashboard-card');
    }
}
