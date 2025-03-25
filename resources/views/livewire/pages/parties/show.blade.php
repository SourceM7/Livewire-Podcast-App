<?php

use App\Models\Listening;
use Livewire\Volt\Component;

new class extends Component {
    public Listening $listeningParty;

    public function mount(Listening $listeningParty)
    {
        $this->listeningParty = $listeningParty;
    }
}; ?>

<div>
    {{ $listeningParty->name}}
</div>
