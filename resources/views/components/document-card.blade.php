@props(['document'])

<div x-data="{ showDeleteModal: false }" class="relative group">
    <div class="max-w-full h-full w-48 bg-white rounded-lg shadow-sm hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex flex-col h-full p-3">
            {{-- Preview Section --}}
            <div class="mb-2 h-32 bg-gray-100 rounded-lg overflow-hidden">
                @if($document->doc_type === 'pdf')
                    <div class="w-full h-full" x-data="{ pdfUrl: '{{ route('documents.download', $document->id) }}' }" x-init="initPdfPreview($el, pdfUrl)">
                        <canvas class="w-full h-full object-cover"></canvas>
                    </div>
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                @endif
            </div>

            {{-- Content Section --}}
            <div class="flex-1">
                <h5 class="text-sm font-bold tracking-tight text-gray-900 mb-1 truncate">
                    {{ $document->doc_name }}
                </h5>
                <p class="text-xs text-gray-700 line-clamp-2 mb-2">
                    {{ Str::limit($document->doc_content, 100) }}
                </p>
            </div>

            {{-- Actions Section --}}
            <div class="mt-auto">
                <div class="flex justify-end gap-1">
                    {{-- Download Button --}}
                    <button 
                        title="Télécharger"
                        onclick="window.location.href='{{ route('documents.download', $document->id) }}'"
                        class="p-1 text-gray-500 hover:text-gray-700 rounded-full hover:bg-gray-100 transition-colors duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                    </button>

                    {{-- Edit Button --}}
                    <button 
                        title="Modifier"
                        onclick="window.location.href='{{ route('documents.edit', $document->id) }}'"
                        class="p-1 text-gray-500 hover:text-gray-700 rounded-full hover:bg-gray-100 transition-colors duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>

                    {{-- Delete Button --}}
                    <button 
                        title="Supprimer"
                        @click="showDeleteModal = true"
                        class="p-1 text-gray-500 hover:text-red-600 rounded-full hover:bg-gray-100 transition-colors duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div x-show="showDeleteModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-90"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-90"
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black opacity-30"></div>
            
            <div class="relative bg-white rounded-lg max-w-md w-full p-6">
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Confirmer la suppression</h3>
                    <p class="mt-2 text-sm text-gray-500">
                        Êtes-vous sûr de vouloir supprimer ce document ? Cette action est irréversible.
                    </p>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button @click="showDeleteModal = false" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Annuler
                    </button>
                    <form action="{{ route('documents.destroy', $document->id) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function initPdfPreview(element, pdfUrl) {
        // Utilisation de pdf.js pour le rendu des aperçus PDF
        pdfjsLib.getDocument(pdfUrl).promise.then(function(pdf) {
            pdf.getPage(1).then(function(page) {
                const canvas = element.querySelector('canvas');
                const context = canvas.getContext('2d');
                const viewport = page.getViewport({ scale: 0.5 });

                canvas.width = viewport.width;
                canvas.height = viewport.height;

                page.render({
                    canvasContext: context,
                    viewport: viewport
                });
            });
        });
    }
</script>
@endpush
