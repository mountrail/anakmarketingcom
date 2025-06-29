<div x-data="musicPlayer()" x-init="init()" wire:ignore class="relative">
    <!-- Audio Element -->
    <audio x-ref="audio" preload="metadata" x-on:loadedmetadata="updateDuration()" x-on:timeupdate="updateTime()"
        x-on:ended="onTrackEnd()" x-on:canplay="isLoading = false" x-on:error="hasError = true">
        <source x-bind:src="getAudioSource()" type="audio/mpeg">
    </audio>

    <!-- Player Container -->
    <div
        class="flex items-center space-x-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-2 ml-4">

        <!-- Previous Button -->
        <button x-on:click="previousTrack()"
            class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path
                    d="M8.445 14.832A1 1 0 0010 14v-2.798l5.445 3.63A1 1 0 0017 14V6a1 1 0 00-1.555-.832L10 8.798V6a1 1 0 00-1.555-.832l-6 4a1 1 0 000 1.664l6 4z" />
            </svg>
        </button>

        <!-- Play/Pause Button -->
        <button x-on:click="togglePlay()"
            class="text-amber-600 hover:text-amber-700 dark:text-amber-500 dark:hover:text-amber-400 transition-colors">
            <svg x-show="!isPlaying" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path
                    d="M6.3 2.841A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z" />
            </svg>
            <svg x-show="isPlaying" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path
                    d="M5.75 3a.75.75 0 00-.75.75v12.5c0 .414.336.75.75.75h1.5a.75.75 0 00.75-.75V3.75A.75.75 0 007.25 3h-1.5zM12.75 3a.75.75 0 00-.75.75v12.5c0 .414.336.75.75.75h1.5a.75.75 0 00.75-.75V3.75a.75.75 0 00-.75-.75h-1.5z" />
            </svg>
        </button>

        <!-- Next Button -->
        <button x-on:click="nextTrack()"
            class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path
                    d="M4.555 5.168A1 1 0 003 6v8a1 1 0 001.555.832L10 11.202V14a1 1 0 001.555.832l6-4a1 1 0 000-1.664l-6-4A1 1 0 0010 6v2.798l-5.445-3.63z" />
            </svg>
        </button>

        <!-- Track Info -->
        <div class="flex flex-col min-w-0 px-2 flex-1">
            <div class="text-xs font-medium text-gray-900 dark:text-gray-100 truncate" x-text="getCurrentTrack().title">
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="getCurrentTrack().artist">
            </div>
        </div>

        <!-- Volume Control -->
        <div class="hidden lg:flex items-center space-x-1">
            <button x-on:click="toggleMute()"
                class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                <svg x-show="!isMuted && volume > 0" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.617.824L4.215 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.215l4.168-3.824a1 1 0 011.617.824z" />
                    <path
                        d="M11.025 7.05a.75.75 0 01.05 1.06A2.5 2.5 0 0112 10a2.5 2.5 0 01-.925 1.89.75.75 0 11-1.01-1.11A1 1 0 0010.5 10a1 1 0 00-.435-.82.75.75 0 011.06-.05z" />
                </svg>
                <svg x-show="isMuted || volume === 0" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.617.824L4.215 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.215l4.168-3.824a1 1 0 011.617.824z" />
                    <path
                        d="M12.293 7.293a1 1 0 011.414 0L15 8.586l1.293-1.293a1 1 0 111.414 1.414L16.414 10l1.293 1.293a1 1 0 01-1.414 1.414L15 11.414l-1.293 1.293a1 1 0 01-1.414-1.414L13.586 10l-1.293-1.293a1 1 0 010-1.414z" />
                </svg>
            </button>
            <input type="range" x-bind:value="volume * 100" max="100"
                x-on:input="setVolume($event.target.value / 100)"
                class="w-16 h-1 bg-gray-200 dark:bg-gray-600 rounded-lg appearance-none cursor-pointer">
        </div>
    </div>

    <!-- Dancing Bunny - Now positioned below the player -->
    <div x-show="showBunny" x-transition:enter="transition ease-out duration-500"
        x-transition:enter-start="opacity-0 transform translate-y-4"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform translate-y-4" class="flex justify-center mt-4">
        <div x-on:click="togglePlay()"
            class="cursor-pointer group bg-gradient-to-br from-amber-400 to-amber-600 rounded-full p-2 shadow-lg border-2 border-white dark:border-gray-700 animate-bounce hover:scale-110 transition-transform duration-200 group-hover:shadow-amber-500/50"
            title="Click to pause music">
            <img src="{{ asset('storage/uploads/gif/bunny-dancing-on-loud-speakers.webp') }}"
                alt="Dancing Bunny - Click to pause" class="w-16 h-16 rounded-full pointer-events-none">
            <!-- Pulse ring animation -->
            <div class="absolute inset-0 rounded-full bg-amber-400 opacity-20 animate-ping space-y-4"></div>
        </div>
    </div>

    <script>
        function musicPlayer() {
            return {
                // Livewire entangled properties
                isPlaying: @entangle('isPlaying'),
                currentTime: @entangle('currentTime'),
                duration: @entangle('duration'),
                volume: @entangle('volume'),
                isMuted: @entangle('isMuted'),
                currentTrack: @entangle('currentTrack'),
                showBunny: @entangle('showBunny'),

                // Local properties
                playlist: @json($playlist),
                musicBasePath: '{!! asset('storage/music') !!}/',
                isLoading: false,
                hasError: false,

                init() {
                    this.$refs.audio.volume = this.volume;
                    this.loadCurrentTrack();
                    this.setupEventListeners();
                },

                setupEventListeners() {
                    this.$wire.on('toggle-play', () => this.handleTogglePlay());
                    this.$wire.on('change-track', () => this.handleTrackChange());
                    this.$wire.on('toggle-mute', () => this.$refs.audio.muted = this.isMuted);
                    this.$wire.on('volume-change', (event) => this.$refs.audio.volume = event.volume);
                    this.$wire.on('seek-to', (event) => this.$refs.audio.currentTime = event.time);
                },

                loadCurrentTrack() {
                    if (this.getCurrentTrack()) {
                        this.$refs.audio.load();
                    }
                },

                handleTogglePlay() {
                    if (this.isPlaying) {
                        this.$refs.audio.play().catch(e => {
                            console.error('Error playing:', e);
                            this.$wire.set('isPlaying', false);
                            this.$wire.set('showBunny', false);
                        });
                    } else {
                        this.$refs.audio.pause();
                    }
                },

                handleTrackChange() {
                    const wasPlaying = this.isPlaying;
                    this.loadCurrentTrack();
                    if (wasPlaying) {
                        this.$refs.audio.addEventListener('canplay', () => {
                            this.$refs.audio.play().catch(e => {
                                console.error('Error playing:', e);
                                this.$wire.set('isPlaying', false);
                                this.$wire.set('showBunny', false);
                            });
                        }, {
                            once: true
                        });
                    }
                },

                async togglePlay() {
                    if (!this.getCurrentTrack() || !this.getCurrentTrack().file) {
                        return;
                    }

                    if (!this.isPlaying) {
                        try {
                            await this.$refs.audio.play();
                            this.$wire.set('isPlaying', true);
                            this.$wire.set('showBunny', true);
                        } catch (error) {
                            console.error('Error playing audio:', error);
                        }
                    } else {
                        this.$refs.audio.pause();
                        this.$wire.set('isPlaying', false);
                        this.$wire.set('showBunny', false);
                    }
                },

                previousTrack() {
                    this.$wire.previousTrack();
                },

                nextTrack() {
                    this.$wire.nextTrack();
                },

                onTrackEnd() {
                    this.$wire.set('showBunny', false);
                    this.$wire.onTrackEnd();
                },

                toggleMute() {
                    this.$wire.toggleMute();
                },

                seek(time) {
                    this.$wire.seek(parseFloat(time));
                },

                setVolume(volume) {
                    this.$wire.updateVolume(parseFloat(volume));
                },

                updateTime() {
                    if (this.$refs.audio && !isNaN(this.$refs.audio.currentTime)) {
                        this.$wire.updateTime(this.$refs.audio.currentTime);
                    }
                },

                updateDuration() {
                    if (this.$refs.audio && !isNaN(this.$refs.audio.duration)) {
                        this.$wire.updateDuration(this.$refs.audio.duration);
                    }
                },

                getCurrentTrack() {
                    return this.playlist && this.playlist[this.currentTrack] ? this.playlist[this.currentTrack] : {
                        title: 'No Track',
                        artist: 'Unknown'
                    };
                },

                getAudioSource() {
                    const track = this.getCurrentTrack();
                    return track && track.file ? this.musicBasePath + track.file : '';
                },

                formatTime(seconds) {
                    if (!seconds || isNaN(seconds)) return '0:00';
                    const mins = Math.floor(seconds / 60);
                    const secs = Math.floor(seconds % 60);
                    return `${mins}:${secs.toString().padStart(2, '0')}`;
                }
            }
        }
    </script>
</div>
