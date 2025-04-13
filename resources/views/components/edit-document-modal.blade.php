@props(['id' => 'edit-document', 'docTypes'])

<div x-data="{ 
    show: false,
    document: null
}" 
    x-show="show" 
    x-cloak
    @open-modal.window="if ($event.detail === '{{ $id }}') { 
        show = true;
        document = $event.detail.document;
    }"
    @close-modal.window="if ($event.detail === '{{ $id }}') show = false"
    @keydown.escape.window="show = false"
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;">
    
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form x-bind:action="'/documents/' + document?.doc_id" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="mb-4">
                        <label for="doc_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nom du document *
                        </label>
                        <input type="text" 
                               name="doc_name" 
                               id="doc_name" 
                               required
                               x-model="document?.doc_name"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="mb-4">
                        <label for="doc_type" class="block text-sm font-medium text-gray-700 mb-2">
                            Type de document *
                        </label>
                        <select name="doc_type" 
                                id="doc_type" 
                                required
                                x-model="document?.doc_type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Sélectionnez un type</option>
                            @foreach($docTypes as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="doc_content" class="block text-sm font-medium text-gray-700 mb-2">
                            Description
                        </label>
                        <textarea name="doc_content" 
                                  id="doc_content"
                                  x-model="document?.doc_content"
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="doc_file" class="block text-sm font-medium text-gray-700 mb-2">
                            Fichier
                        </label>
                        <input type="file" 
                               name="doc_file" 
                               id="doc_file"
                               accept=".pdf"
                               class="w-full">
                        <p class="mt-1 text-sm text-gray-500">
                            Laissez vide pour conserver le fichier actuel
                        </p>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Mettre à jour
                    </button>
                    <button type="button"
                            @click="show = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
