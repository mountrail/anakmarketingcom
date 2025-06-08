<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Storage;

class MusicPlayer extends Component
{
    public $isPlaying = false;
    public $currentTime = 0;
    public $duration = 0;
    public $volume = 0.5;
    public $isMuted = false;
    public $currentTrack = 0;
    public $showBunny = false; // Add this property

    public $playlist = [];

    public function mount()
    {
        // Load music files from storage
        $this->loadPlaylist();

        // Initialize with first track
        $this->currentTrack = 0;
    }

    protected function loadPlaylist()
    {
        // Check if music directory exists and has files
        if (Storage::disk('public')->exists('music')) {
            $musicFiles = Storage::disk('public')->files('music');

            foreach ($musicFiles as $file) {
                $filename = basename($file);
                $name = pathinfo($filename, PATHINFO_FILENAME);

                // Try to parse artist and title from filename
                if (strpos($name, ' - ') !== false) {
                    list($artist, $title) = explode(' - ', $name, 2);
                } else {
                    $artist = 'Unknown Artist';
                    $title = $name;
                }

                $this->playlist[] = [
                    'title' => $title,
                    'artist' => $artist,
                    'file' => $filename
                ];
            }
        }

        // Fallback if no files found
        if (empty($this->playlist)) {
            $this->playlist = [
                [
                    'title' => 'Angel',
                    'artist' => 'Shaggy',
                    'file' => 'Shaggy - Angel.mp3'
                ],
            ];
        }
    }

    public function togglePlay()
    {
        $this->isPlaying = !$this->isPlaying;
        $this->showBunny = $this->isPlaying; // Show/hide bunny based on playing state
        $this->dispatch('toggle-play');
    }

    public function previousTrack()
    {
        $this->currentTrack = ($this->currentTrack - 1 + count($this->playlist)) % count($this->playlist);
        $this->dispatch('change-track', trackIndex: $this->currentTrack);
    }

    public function nextTrack()
    {
        $this->currentTrack = ($this->currentTrack + 1) % count($this->playlist);
        $this->dispatch('change-track', trackIndex: $this->currentTrack);
    }

    public function toggleMute()
    {
        $this->isMuted = !$this->isMuted;
        $this->dispatch('toggle-mute');
    }

    public function updateTime($time)
    {
        $this->currentTime = (float) $time;
    }

    public function updateDuration($duration)
    {
        $this->duration = (float) $duration;
    }

    public function updateVolume($volume)
    {
        $this->volume = (float) $volume;
        $this->dispatch('volume-change', volume: $volume);
    }

    public function seek($time)
    {
        $this->currentTime = (float) $time;
        $this->dispatch('seek-to', time: $time);
    }

    // Add method to handle when track ends
    public function onTrackEnd()
    {
        $this->showBunny = false; // Hide bunny when track ends
        $this->nextTrack();
    }

    public function getCurrentTrack()
    {
        return $this->playlist[$this->currentTrack] ?? ($this->playlist[0] ?? null);
    }

    public function render()
    {
        return view('livewire.music-player');
    }
}
