{{-- resources\views\posts\partials\answers-list.blade.php --}}
@if ($post->answers->count() > 0)
    <div class="space-y-8">
        @foreach ($post->answers->sortByDesc('is_editors_pick')->sortByDesc('created_at') as $answer)
            <div class="border-b pb-6 last:border-b-0" id="answer-{{ $answer->id }}" x-data="{
                editing: false,
                content: @js(strip_tags($answer->content)),
                originalContent: @js(strip_tags($answer->content)),
                saving: false,

                startEdit() {
                    this.editing = true;
                    this.content = this.originalContent;
                    this.$nextTick(() => {
                        // Fokus pada textarea setelah terlihat
                        this.$refs.editTextarea?.focus();
                    });
                },

                cancelEdit() {
                    this.editing = false;
                    this.content = this.originalContent;
                },

                async saveEdit() {
                    if (this.content.trim().length < 5) {
                        alert('Konten harus memiliki setidaknya 5 karakter.');
                        return;
                    }

                    this.saving = true;

                    try {
                        const response = await fetch(`/answers/{{ $answer->id }}`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                content: this.content
                            })
                        });

                        if (response.ok) {
                            const data = await response.json();
                            this.originalContent = this.content;
                            this.editing = false;

                            // Perbarui tampilan konten dengan baris baru
                            const displayContent = this.content.replace(/\n/g, '<br>');
                            document.querySelector('#answer-content-{{ $answer->id }}').innerHTML = displayContent;

                            // Tampilkan pesan sukses
                            this.showMessage('Jawaban berhasil diperbarui!', 'success');
                        } else {
                            throw new Error('Gagal memperbarui jawaban');
                        }
                    } catch (error) {
                        console.error('Error memperbarui jawaban:', error);
                        this.showMessage('Gagal memperbarui jawaban. Silakan coba lagi.', 'error');
                    } finally {
                        this.saving = false;
                    }
                },

                showMessage(message, type) {
                    // Notifikasi toast sederhana
                    const toast = document.createElement('div');
                    toast.className = `fixed top-4 right-4 p-4 rounded-md z-50 ${type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'}`;
                    toast.textContent = message;
                    document.body.appendChild(toast);

                    setTimeout(() => {
                        toast.remove();
                    }, 3000);
                }
            }"
                @edit-answer.window="if ($event.detail.id === {{ $answer->id }}) startEdit()">

                <!-- Header Jawaban - Profil, Info Pengguna, dan Lencana -->
                <div class="mb-4">
                    <x-user-profile-info :user="$answer->user" :timestamp="$answer->created_at" badgeSize="w-10 h-10" mobileBadgeSize="w-7 h-7" profileSize="h-12 w-12"
                        :showJobInfo="true">

                        <x-slot name="additionalBadges">
                            @if ($answer->is_editors_pick)
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                    </svg>
                                    Editor's Picks
                                </span>
                            @endif
                        </x-slot>
                    </x-user-profile-info>
                </div>

                <!-- Konten Jawaban - Mode Tampilan -->
                <div x-show="!editing" class="mt-3 text-gray-900 dark:text-gray-100"
                    id="answer-content-{{ $answer->id }}">
                    {!! nl2br(e(strip_tags($answer->content))) !!}
                </div>

                <!-- Konten Jawaban - Mode Edit -->
                <div x-show="editing" x-cloak class="mt-3">
                    <div class="mb-4">
                        <label for="edit-content-{{ $answer->id }}"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Edit jawaban Anda
                        </label>
                        <textarea x-ref="editTextarea" x-model="content" id="edit-content-{{ $answer->id }}" rows="6"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-700 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-100 sm:text-sm resize-none"
                            placeholder="Edit jawaban Anda..." required></textarea>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Minimal 5 karakter diperlukan. Gunakan Enter untuk baris baru.
                        </p>
                    </div>

                    <!-- Tombol Aksi Edit -->
                    <div class="flex items-center space-x-3">
                        <x-primary-button @click="saveEdit()" :disabled="false"
                            x-bind:disabled="saving || content.trim().length < 5" variant="primary" size="md">
                            <span x-show="!saving">Simpan</span>
                            <span x-show="saving" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Menyimpan...
                            </span>
                        </x-primary-button>

                        <x-primary-button @click="cancelEdit()" variant="inactive" size="md">
                            Batal
                        </x-primary-button>
                    </div>
                </div>

                <!-- Bilah Aksi -->
                <div class="mt-4" x-show="!editing">
                    <x-action-bar :model="$answer" modelType="answer" :showVoteScore="false" :showCommentCount="false"
                        :showShare="false" customClasses="justify-start" />
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="p-6 text-center border rounded-md border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
        <p class="text-gray-500 dark:text-gray-400">
            Belum ada jawaban. Jadilah yang pertama berbagi pengetahuan Anda!
        </p>
    </div>
@endif

@push('styles')
    <style>
        /* Sembunyikan elemen dengan x-cloak hingga Alpine.js diinisialisasi */
        [x-cloak] {
            display: none !important;
        }

        /* Transisi halus untuk mode edit */
        .answer-transition {
            transition: all 0.3s ease;
        }

        /* Gaya fokus untuk textarea */
        textarea:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Nonaktifkan resize textarea */
        textarea {
            resize: none;
        }
    </style>
@endpush
