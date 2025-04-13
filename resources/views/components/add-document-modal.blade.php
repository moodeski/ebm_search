@props(['docTypes'])

<div x-data="{ 
    show: false,
    isUploading: false,
    fileName: '',
    success: false,
    error: null
}" 
    @keydown.escape.window="show = false"
    @document-uploaded.window="success = true; setTimeout(() => { show = false; success = false }, 2000)">

    {{-- Trigger Button --}}
    <button @click="show = true" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition duration-200">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        <span>Ajouter un document</span>
    </button>

    {{-- Modal --}}
    <div x-show="show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-90"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-90"
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;">
        
        {{-- Overlay --}}
        <div class="fixed inset-0 bg-black opacity-30"></div>

        {{-- Modal Content --}}
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="relative bg-white rounded-lg max-w-md w-full p-6">
                {{-- Success Message --}}
                <div x-show="success" 
                     x-transition
                     class="absolute top-0 left-0 right-0 px-4 py-3 bg-green-500 text-white text-center transform -translate-y-full">
                    Document ajouté avec succès !
                </div>

                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Ajouter un nouveau document</h3>
                </div>

                <form action="{{ route('documents.store') }}" 
                      method="POST" 
                      enctype="multipart/form-data"
                      @submit.prevent="submitForm($event)">
                    @csrf

                    {{-- Nom du document --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Nom du document*</label>
                        <input type="text" 
                               name="doc_name" 
                               required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                    </div>

                    {{-- Type de document --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Type de document*</label>
                        <select name="doc_type" 
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                            @foreach($docTypes as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Upload de fichier --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Fichier* (PDF, Word ou TXT)</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md"
                             x-data="{ dragover: false }"
                             @dragover.prevent="dragover = true"
                             @dragleave.prevent="dragover = false"
                             @drop.prevent="dragover = false; handleFileDrop($event)">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label class="relative cursor-pointer bg-white rounded-md font-medium text-purple-600 hover:text-purple-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-purple-500">
                                        <span>Téléverser un fichier</span>
                                        <input type="file" 
                                               name="doc_file" 
                                               class="sr-only" 
                                               accept=".pdf,.doc,.docx,.txt" 
                                               required
                                               @change="fileName = $event.target.files[0].name">
                                    </label>
                                    <p class="pl-1">ou glisser-déposer</p>
                                </div>
                                <p class="text-xs text-gray-500" x-text="fileName || 'PDF, Word ou TXT uniquement'"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Error Message --}}
                    <div x-show="error" 
                         x-text="error"
                         class="mb-4 text-sm text-red-600"></div>

                    {{-- Buttons --}}
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" 
                                @click="show = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                            Annuler
                        </button>
                        <button type="submit"
                                :disabled="isUploading"
                                class="px-4 py-2 text-sm font-medium text-white bg-purple-600 border border-transparent rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50">
                            <span x-show="!isUploading">Enregistrer</span>
                            <span x-show="isUploading">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function handleFileDrop(event) {
        const file = event.dataTransfer.files[0];
        if (file) {
            const input = this.$el.querySelector('input[type="file"]');
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            input.files = dataTransfer.files;
            this.fileName = file.name;
        }
    }

    async function submitForm(event) {
        const form = event.target;
        const formData = new FormData(form);
        
        this.isUploading = true;
        this.error = null;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (!response.ok) {
                const data = await response.json();
                throw new Error(data.message || 'Une erreur est survenue lors de l\'envoi du document');
            }

            const data = await response.json();
            this.success = true;
            setTimeout(() => {
                this.show = false;
                this.success = false;
                window.dispatchEvent(new CustomEvent('documents-updated'));
            }, 2000);
            
        } catch (error) {
            this.error = error.message;
        } finally {
            this.isUploading = false;
        }
    }
</script>
@endpush
