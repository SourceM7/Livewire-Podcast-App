<?php

use App\Jobs\ProcessPodcastUrl;
use App\Models\Episode;
use App\Models\Listening;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new class extends Component {
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required')]
    public $startTime;

    #[Validate('required|url')]
    public string $mediaUrl = '';

    public function createListeningParty()
    {
        $this->validate();

        $episode = Episode::create([
            'media_url' => $this->mediaUrl,
        ]);

        $listeningParty = Listening::create([
            'episode_id' => $episode->id,
            'name' => $this->name,
            'start_time' => $this->startTime,
        ]);

        ProcessPodcastUrl::dispatch($this->mediaUrl,$listeningParty,$episode);

        return redirect()->route('parties.show', $listeningParty);
    }
    public function with()
    {
        return [
            'listening_parties' => Listening::all(),
        ];
    }
}; ?>

<div class="flex items-center justify-center min-h-screen bg-slate-50 p-4">
    <form wire:submit='createListeningParty'>
        <x-input wire:model='name' placeholder="Listening Party Name" />
        <x-input wire:model='mediaUrl' placeholder="Podcast RSS Feed URL"
            description="Entering the RSS Feed URL will grab the latest episode" />
        <x-datetime-picker wire:model='startTime' placeholder='Listening PartyStart Time' :min="now()"/>
        <x-button type="submit" class="w-full">Create Listening Party</x-button>

    </form>
</div>
