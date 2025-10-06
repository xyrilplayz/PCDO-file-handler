<form action="{{ route('progress.store', $program->id) }}" method="POST" enctype="multipart/form-data" class="max-w-2xl mx-auto bg-white p-8 rounded-2xl shadow-md space-y-6">
    @csrf

    <h2 class="text-2xl font-semibold text-gray-800 text-center">Upload Progress Report</h2>

    <!-- Cooperative Program -->
    <div>
        <label for="coop_program_id" class="block text-sm font-medium text-gray-700 mb-1">
            Select Cooperative Program
        </label>
        <select name="coop_program_id" id="coop_program_id" required
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">-- Select Cooperative --</option>
            @foreach($coopPrograms as $coopProgram)
                <option value="{{ $coopProgram->id }}">
                    {{ $coopProgram->cooperative->name }}
                </option>
            @endforeach
        </select>
    </div>

    <!-- Title -->
    <div>
        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
            Progress Title
        </label>
        <input type="text" name="title" id="title" required
            placeholder="Enter progress title"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
    </div>

    <!-- Description -->
    <div>
        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
            Description
        </label>
        <textarea name="description" id="description" rows="3"
            placeholder="Add a short description..."
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
    </div>

    <!-- File Upload -->
    <div>
        <label for="file" class="block text-sm font-medium text-gray-700 mb-2">
            Upload Images
        </label>
        <div class="flex items-center justify-center w-full">
            <label for="file"
                class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 border-gray-300">
                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                    <p class="text-xs text-gray-500">PNG, JPG, or JPEG (multiple allowed)</p>
                </div>
                <input id="file" type="file" name="file[]" multiple accept="image/*" class="hidden">
            </label>
        </div>
    </div>

    <!-- Submit Button -->
    <div class="text-center">
        <button type="submit"
            class="bg-indigo-600 text-white font-semibold px-6 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
            Upload Progress
        </button>
    </div>
</form>
