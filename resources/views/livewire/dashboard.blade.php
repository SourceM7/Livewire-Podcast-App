<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Validate;
use App\Models\Listening;
use App\Models\Episode;
use App\Jobs\ProcessPodcastUrl;

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

        $Listening = Listening::create([
            'episode_id' => $episode->id,
            'name' => $this->name,
            'start_time' => $this->startTime,
        ]);

        ProcessPodcastUrl::dispatch($this->mediaUrl, $Listening, $episode);

        return redirect()->route('parties.show', $Listening);
    }

    public function with()
    {
        return [
            'listeningParties' => Listening::where('is_active', true)->whereNotNull('end_time')->orderBy('start_time', 'asc')->with('episode.podcast')->get(),
        ];
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="flex items-center justify-center min-h-screen bg-emerald-50">

                <div class="flex items-center justify-center space-x-8">
                    <div class="relative flex items-center justify-center w-16 h-16">
                        <span
                            class="absolute inline-flex rounded-full opacity-75 size-10 bg-emerald-400 animate-ping"></span>
                        <span
                            class="relative inline-flex items-center justify-center text-2xl font-bold text-white rounded-full size-12 bg-emerald-500">
                            🫶
                            </svg>
                        </span>
                    </div>

            </div>
        </div>
        HTML;
    }
}; ?>

<div class="flex flex-col min-h-screen pt-8 bg-emerald-50">
    {{-- Top Half: Create New Listening Party Form --}}
    <div class="flex items-center justify-center p-4">
        <div class="w-full max-w-lg">
            <x-card shadow="lg" rounded="lg">
                <h2 class="font-serif text-xl font-bold text-center">Let's listen together.</h2>
                <form wire:submit='createListeningParty' class="mt-6 space-y-6">
                    <x-input wire:model='name' placeholder="Listening Party Name" />
                    <x-input wire:model='mediaUrl' placeholder="Podcast RSS Feed URL"
                        description="Entering the RSS Feed URL will grab the latest episode" />
                    <x-datetime-picker wire:model='startTime' placeholder="Listening Party Start Time"
                        :min="now()->subDays(1)" />
                    <x-button type="submit" class="w-full">Create Listening Party</x-button>
                </form>
            </x-card>
        </div>
    </div>
    {{-- Bottom Half: Existing Listening Parties --}}
    <div class="my-20">
        <div class="max-w-lg mx-auto">
            <h3 class="mb-4 font-serif text-[0.9rem] font-bold">Upcoming Listening Parties</h3>
            <div class="bg-white rounded-lg shadow-lg">
                @if ($listeningParties->isEmpty())
                    <div class="flex items-center justify-center p-6 font-serif text-sm">No Audio Started Yet 🎙️</div>
                @else
                    @foreach ($listeningParties as $Listening)
                        <div wire:key="{{ $Listening->id }}">
                            <a href="{{ route('parties.show', $Listening) }}" class="block">
                                <div
                                    class="flex items-center justify-between p-4 transition-all duration-150 ease-in-out border-b border-gray-200 hover:bg-gray-50">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            <x-avatar src="{{ $Listening->episode->podcast->artwork_url }}"
                                                size="xl" rounded="sm" alt="Podcast Artwork" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[0.9rem] font-semibold truncate text-slate-900">
                                                {{ $Listening->name }}</p>
                                            <div class="mt-0.8">
                                                <p class="max-w-xs text-sm truncate text-slate-600">
                                                    {{ $Listening->episode->title }}</p>
                                                <p class="text-[0.7rem] tracking-tighter uppercase text-slate-400">
                                                    {{ $Listening->podcast->title }}</p>
                                            </div>
                                            <div class="mt-1 text-xs text-slate-600" x-data="{
                                                startTime: {{ $Listening->start_time->timestamp }},
                                                countdownText: '',
                                                isLive: {{ $Listening->start_time->isPast() && $Listening->is_active ? 'true' : 'false' }},
                                                updateCountdown() {
                                                    const now = Math.floor(Date.now() / 1000);
                                                    const timeUntilStart = this.startTime - now;
                                                    if (timeUntilStart <= 0) {
                                                        this.countdownText = 'Live';
                                                        this.isLive = true;
                                                    } else {
                                                        const days = Math.floor(timeUntilStart / 86400);
                                                        const hours = Math.floor((timeUntilStart % 86400) / 3600);
                                                        const minutes = Math.floor((timeUntilStart % 3600) / 60);
                                                        const seconds = timeUntilStart % 60;
                                                        this.countdownText = `${days}d ${hours}h ${minutes}m ${seconds}s`;
                                                    }
                                                }
                                            }"
                                                x-init="updateCountdown();
                                                setInterval(() => updateCountdown(), 1000);">
                                                <div x-show="isLive">
                                                    <x-badge flat rose label="Live">
                                                        <x-slot name="prepend"
                                                            class="relative flex items-center w-2 h-2">
                                                            <span
                                                                class="absolute inline-flex w-full h-full rounded-full opacity-75 bg-rose-500 animate-ping"></span>

                                                            <span
                                                                class="relative inline-flex w-2 h-2 rounded-full bg-rose-500"></span>
                                                        </x-slot>

                                                    </x-badge>

                                                </div>
                                                <div x-show="!isLive">
                                                    Starts in: <span x-text="countdownText"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <x-button flat xs class="w-20">Join</x-button>
                                </div>
                            </a>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
