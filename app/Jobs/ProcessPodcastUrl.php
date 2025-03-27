<?php

namespace App\Jobs;

use App\Models\Podcast;
use Carbon\CarbonInterval;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessPodcastUrl implements ShouldQueue
{
    use Queueable;

    public $rssUrl;

    public $listeningParty;

    public $episode;

    /**
     * Create a new job instance.
     */
    public function __construct($rssUrl, $listeningParty, $episode)
    {
        $this->rssUrl = $rssUrl;
        $this->listeningParty = $listeningParty;
        $this->episode = $episode;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $xml = simplexml_load_file($this->rssUrl);

        $podcastTitle = $xml->channel->title;
        $podcastArtworkUrl = $xml->channel->image->url;

        $latestEpisode = $xml->channel->item[0];

        $episodeTitle = $latestEpisode->title;
        $episodeMediaUrl = (string) $latestEpisode->enclosure['url'];

        $namespaces = $xml->getNamespaces(true);
        $itunesNamespace = $namespaces['itunes'] ?? null;

        $episodeLength = null;

        if ($itunesNamespace) {
            $episodeLength = (string) $latestEpisode->children($itunesNamespace)->duration;
        }

        if (empty($episodeLength)) {
            $fileSize = (int) $latestEpisode->enclosure['length'];
            $bitrate = 128000;
            $durationInSeconds = ceil($fileSize * 8 / $bitrate);
            $episodeLength = (string) $durationInSeconds;
        }

        
        try {
            if (strpos($episodeLength, ':') !== false) {
                
                $parts = explode(':', $episodeLength);
                if (count($parts) == 2) {
                    $interval = CarbonInterval::createFromFormat('i:s', $episodeLength);
                } elseif (count($parts) == 3) {
                    $interval = CarbonInterval::createFromFormat('H:i:s', $episodeLength);
                } else {
                    throw new \Exception('Unexpected duration format');
                }
            } else {
                $interval = CarbonInterval::seconds((int) $episodeLength);
            }
        } catch (\Exception $e) {
            Log::error('Error parsing episode duration: '.$e->getMessage());
            $interval = CarbonInterval::hour();
        }

        $endTime = $this->listeningParty->start_time->add($interval);


        $podcast = Podcast::updateOrCreate([
            'title' => $podcastTitle,
            'artwork_url' => $podcastArtworkUrl,
            'rss_url' => $this->rssUrl,
        ]);

        $this->episode->podcast()->associate($podcast);

        $this->episode->update([
            'title' => $episodeTitle,
            'media_url' => $episodeMediaUrl,
        ]);

        $this->listeningParty->update([
            'end_time' => $endTime,
        ]);

    }
}
