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

        ProcessPodcastUrl::dispatch($this->mediaUrl, $listeningParty, $episode);

        return redirect()->route('parties.show', $listeningParty);
    }
    public function with()
    {
        return [
            'listeningParties' => Listening::where('is_active', true)->orderBy('start_time', 'asc')->with('episode.podcast')->get(),
        ];
    }
}; ?>
<div class="min-h-screen bg-emerald-50 flex flex-col pt-8">
    <div class="flex items-center justify-center min-h-screen bg-slate-50 p-4">
        <div class="w-full max-w-lg">
            <x-card shadow="lg" rounded="lg">
                <form wire:submit='createListeningParty' class="mt-6 space-y-6">
                    <x-input wire:model='name' placeholder="Listening Party Name" />
                    <x-input wire:model='mediaUrl' placeholder="Podcast RSS Feed URL"
                        description="Entering the RSS Feed URL will grab the latest episode" />
                    <x-datetime-picker wire:model='startTime' placeholder='Listening PartyStart Time' :min="now()" />
                    <x-button type="submit" class="w-full">Create Listening Party</x-button>

                </form>
            </x-card>
        </div>
    </div>

    <div class="my-20">
        @if($listeningParties->isEmpty())
        <div>No audio listening paries started yet</div>
        @else
        @foreach($listeningParties as $listeningParty)
        <div wire:key="{{$listeningParty->id}}">
            <x-avatar src="{{$listeningParty->episode->podcast->artwork_url}}" size="xl" rounded="full"/>
                <p>{{$listeningParty->name}}</p>
                <p>{{$listeningParty->episode->title}}</p>
                <p>{{$listeningParty->podcast->title}}</p>
                <p>{{$listeningParty->start_time}}</p>
                
            </div>
            @endforeach
        @endif
            
    </div>
</div>
