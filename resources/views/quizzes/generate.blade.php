@extends('layouts.app')

@section('title', 'Instant Quiz Generator')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-12">
    {{-- Header --}}
    <div class="text-center mb-10">
        <div class="w-16 h-16 bg-amber-100 rounded-2xl flex items-center justify-center mx-auto mb-4 bounce-in">
            <i data-lucide="zap" class="w-8 h-8 text-amber-600"></i>
        </div>
        <h1 class="text-3xl md:text-4xl font-extrabold text-gray-800 mb-3">Instant Quiz Generator</h1>
        <p class="text-gray-500 text-lg">Pick a source, category, and difficulty — we'll generate a unique quiz in seconds!</p>
    </div>

    {{-- Generator Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-1 gradient-warm"></div>

        <form action="{{ route('quizzes.generate.post') }}" method="POST" id="generateForm">
            @csrf

            {{-- Source Toggle --}}
            <div class="mb-8">
                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-1.5">
                    <i data-lucide="radio" class="w-4 h-4 text-indigo-500"></i> Question Source
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="source" value="online" class="hidden peer" checked>
                        <div class="peer-checked:ring-2 peer-checked:ring-blue-500 peer-checked:bg-blue-50 peer-checked:border-blue-300
                                    bg-gray-50 border border-gray-200 rounded-xl p-4 text-center transition-all duration-200
                                    hover:bg-gray-100 hover:border-gray-300">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                                <i data-lucide="wifi" class="w-5 h-5 text-blue-600"></i>
                            </div>
                            <span class="text-sm font-bold text-blue-600 block">Online</span>
                            <span class="text-xs text-gray-400 block">Fresh from the internet</span>
                            <span class="text-xs text-emerald-500 font-medium flex items-center justify-center gap-1 mt-1">
                                <i data-lucide="check" class="w-3 h-3"></i> Thousands of Qs
                            </span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="source" value="offline" class="hidden peer">
                        <div class="peer-checked:ring-2 peer-checked:ring-purple-500 peer-checked:bg-purple-50 peer-checked:border-purple-300
                                    bg-gray-50 border border-gray-200 rounded-xl p-4 text-center transition-all duration-200
                                    hover:bg-gray-100 hover:border-gray-300">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                                <i data-lucide="package" class="w-5 h-5 text-purple-600"></i>
                            </div>
                            <span class="text-sm font-bold text-purple-600 block">Offline</span>
                            <span class="text-xs text-gray-400 block">Built-in question bank</span>
                            <span class="text-xs text-gray-400 font-medium block mt-1">No internet needed</span>
                        </div>
                    </label>
                </div>
                <div id="onlineBadge" class="mt-3 bg-blue-50 border border-blue-200 rounded-lg p-3 text-xs text-blue-700 flex items-center gap-2">
                    <i data-lucide="globe" class="w-4 h-4 flex-shrink-0"></i>
                    <span>Powered by <strong>Open Trivia Database</strong> — thousands of community-verified questions!</span>
                </div>
                <div id="offlineBadge" class="mt-3 bg-purple-50 border border-purple-200 rounded-lg p-3 text-xs text-purple-700 items-center gap-2 hidden" style="display:none;">
                    <i data-lucide="package" class="w-4 h-4 flex-shrink-0"></i>
                    <span>Using 200 built-in questions across 10 categories. Works without internet!</span>
                </div>
            </div>

            {{-- Category Selection --}}
            <div class="mb-8">
                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-1.5">
                    <i data-lucide="target" class="w-4 h-4 text-indigo-500"></i> Choose a Category
                </label>

                @php
                    $onlineCatIcons = [
                        'General Knowledge' => 'lightbulb',
                        'Science & Nature' => 'flask-conical',
                        'Science: Computers' => 'cpu',
                        'Mathematics' => 'calculator',
                        'History' => 'landmark',
                        'Geography' => 'globe',
                        'Movies' => 'clapperboard',
                        'Music' => 'music',
                        'Television' => 'tv',
                        'Video Games' => 'gamepad-2',
                        'Sports' => 'medal',
                        'Animals' => 'paw-print',
                        'Mythology' => 'swords',
                        'Art' => 'palette',
                        'Celebrities' => 'star',
                        'Vehicles' => 'car',
                        'Comics' => 'book-open',
                        'Anime & Manga' => 'sparkles',
                        'Cartoons' => 'smile',
                    ];
                    $offlineCatIcons = [
                        'Science' => 'flask-conical',
                        'History' => 'landmark',
                        'Geography' => 'globe',
                        'Movies' => 'clapperboard',
                        'Technology' => 'monitor',
                        'Sports' => 'medal',
                        'Music' => 'music',
                        'Food' => 'utensils',
                        'Animals' => 'paw-print',
                        'General Knowledge' => 'lightbulb',
                    ];
                @endphp

                {{-- Online Categories --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3" id="onlineCategoryGrid">
                    @foreach($onlineCategories as $category)
                    <label class="category-card relative cursor-pointer">
                        <input type="radio" name="category" value="{{ $category }}"
                               class="hidden peer online-cat" required data-max="50"
                               {{ old('category') === $category ? 'checked' : '' }}>
                        <div class="peer-checked:ring-2 peer-checked:ring-indigo-500 peer-checked:bg-indigo-50 peer-checked:border-indigo-300
                                    bg-gray-50 border border-gray-200 rounded-xl p-3 text-center transition-all duration-200
                                    hover:bg-gray-100 hover:border-gray-300 hover:shadow-sm">
                            <div class="w-9 h-9 bg-indigo-100 rounded-lg flex items-center justify-center mx-auto mb-1.5">
                                <i data-lucide="{{ $onlineCatIcons[$category] ?? 'book-open' }}" class="w-4.5 h-4.5 text-indigo-600"></i>
                            </div>
                            <span class="text-xs font-semibold text-gray-700 block leading-tight">{{ $category }}</span>
                            <span class="text-xs text-blue-400 flex items-center justify-center gap-0.5 mt-0.5">
                                <i data-lucide="wifi" class="w-2.5 h-2.5"></i> Online
                            </span>
                        </div>
                    </label>
                    @endforeach
                </div>

                {{-- Offline Categories --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 hidden" id="offlineCategoryGrid">
                    @foreach($offlineCategories as $category)
                    <label class="category-card relative cursor-pointer">
                        <input type="radio" name="category" value="{{ $category }}"
                               class="hidden peer offline-cat" data-max="{{ $availableCounts[$category] }}"
                               {{ old('category') === $category ? 'checked' : '' }}>
                        <div class="peer-checked:ring-2 peer-checked:ring-indigo-500 peer-checked:bg-indigo-50 peer-checked:border-indigo-300
                                    bg-gray-50 border border-gray-200 rounded-xl p-4 text-center transition-all duration-200
                                    hover:bg-gray-100 hover:border-gray-300 hover:shadow-sm">
                            <div class="w-9 h-9 bg-indigo-100 rounded-lg flex items-center justify-center mx-auto mb-1.5">
                                <i data-lucide="{{ $offlineCatIcons[$category] ?? 'book-open' }}" class="w-4.5 h-4.5 text-indigo-600"></i>
                            </div>
                            <span class="text-sm font-semibold text-gray-700 block">{{ $category }}</span>
                            <span class="text-xs text-gray-400">{{ $availableCounts[$category] }} Qs</span>
                        </div>
                    </label>
                    @endforeach
                </div>
                @error('category')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            {{-- Number of Questions --}}
            <div class="mb-8">
                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-1.5">
                    <i data-lucide="hash" class="w-4 h-4 text-indigo-500"></i>
                    Number of Questions: <span id="numDisplay" class="text-indigo-600">5</span>
                </label>
                <div class="relative">
                    <input type="range" name="num_questions" id="numQuestions"
                           min="3" max="50" value="{{ old('num_questions', 5) }}"
                           class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                    <div class="flex justify-between text-xs text-gray-400 mt-1.5 px-1">
                        <span>3</span><span>Quick</span><span>15</span><span>Challenge</span><span id="maxLabel">50</span>
                    </div>
                </div>
                @error('num_questions')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            {{-- Difficulty --}}
            <div class="mb-8">
                <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-1.5">
                    <i data-lucide="gauge" class="w-4 h-4 text-indigo-500"></i> Difficulty Level
                </label>
                <div class="grid grid-cols-3 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="difficulty" value="easy" class="hidden peer" {{ old('difficulty', 'medium') === 'easy' ? 'checked' : '' }}>
                        <div class="peer-checked:ring-2 peer-checked:ring-emerald-500 peer-checked:bg-emerald-50 peer-checked:border-emerald-300
                                    bg-gray-50 border border-gray-200 rounded-xl p-4 text-center transition-all duration-200
                                    hover:bg-gray-100 hover:border-gray-300">
                            <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                                <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                            </div>
                            <span class="text-sm font-bold text-emerald-600">Easy</span>
                            <span class="text-xs text-gray-400 block">30s per Q</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="difficulty" value="medium" class="hidden peer" {{ old('difficulty', 'medium') === 'medium' ? 'checked' : '' }}>
                        <div class="peer-checked:ring-2 peer-checked:ring-amber-500 peer-checked:bg-amber-50 peer-checked:border-amber-300
                                    bg-gray-50 border border-gray-200 rounded-xl p-4 text-center transition-all duration-200
                                    hover:bg-gray-100 hover:border-gray-300">
                            <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                                <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                            </div>
                            <span class="text-sm font-bold text-amber-600">Medium</span>
                            <span class="text-xs text-gray-400 block">20s per Q</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="difficulty" value="hard" class="hidden peer" {{ old('difficulty', 'medium') === 'hard' ? 'checked' : '' }}>
                        <div class="peer-checked:ring-2 peer-checked:ring-red-500 peer-checked:bg-red-50 peer-checked:border-red-300
                                    bg-gray-50 border border-gray-200 rounded-xl p-4 text-center transition-all duration-200
                                    hover:bg-gray-100 hover:border-gray-300">
                            <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                                <span class="w-3 h-3 rounded-full bg-red-500"></span>
                            </div>
                            <span class="text-sm font-bold text-red-600">Hard</span>
                            <span class="text-xs text-gray-400 block">15s per Q</span>
                        </div>
                    </label>
                </div>
                @error('difficulty')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            {{-- Preview --}}
            <div id="previewBox" class="bg-gradient-to-r from-indigo-50 to-indigo-100 border border-indigo-200 rounded-xl p-5 mb-8 hidden">
                <h3 class="font-bold text-indigo-800 text-sm mb-3 flex items-center gap-1.5">
                    <i data-lucide="eye" class="w-4 h-4"></i> Your Quiz Preview
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                    <div>
                        <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center mx-auto mb-1 shadow-sm" id="previewSourceWrap">
                            <i data-lucide="wifi" id="previewSourceIcon" class="w-4 h-4 text-blue-500"></i>
                        </div>
                        <span class="text-xs text-gray-500" id="previewSource">Online</span>
                    </div>
                    <div>
                        <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center mx-auto mb-1 shadow-sm" id="previewCatWrap">
                            <i data-lucide="book-open" id="previewCatIcon" class="w-4 h-4 text-indigo-500"></i>
                        </div>
                        <span class="text-xs text-gray-500" id="previewCat">—</span>
                    </div>
                    <div>
                        <span class="text-lg font-bold text-indigo-600 block" id="previewNum">5</span>
                        <span class="text-xs text-gray-500">Questions</span>
                    </div>
                    <div>
                        <div class="w-6 h-6 rounded-full mx-auto mb-1" id="previewDiffDot" style="background:#f59e0b;"></div>
                        <span class="text-xs text-gray-500" id="previewDiff">Medium</span>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <button type="submit" id="generateBtn"
                    class="w-full gradient-warm text-white font-bold py-4 px-8 rounded-xl text-lg
                           hover:opacity-90 transform hover:scale-[1.01] transition-all duration-200
                           shadow-lg hover:shadow-xl flex items-center justify-center gap-3">
                <i data-lucide="zap" class="w-5 h-5"></i>
                <span>Generate Quiz Instantly</span>
            </button>
        </form>
    </div>

    {{-- Info --}}
    <div class="text-center mt-8 text-gray-400 text-sm space-y-1.5">
        <p class="flex items-center justify-center gap-1"><i data-lucide="wifi" class="w-3.5 h-3.5"></i> <strong>Online mode</strong> fetches from <a href="https://opentdb.com" target="_blank" class="underline hover:text-indigo-500">Open Trivia Database</a></p>
        <p class="flex items-center justify-center gap-1"><i data-lucide="package" class="w-3.5 h-3.5"></i> <strong>Offline mode</strong> uses 200 built-in questions</p>
        <p class="flex items-center justify-center gap-1"><i data-lucide="lightbulb" class="w-3.5 h-3.5"></i> If online fails, we automatically fall back to offline</p>
    </div>
</div>

@push('scripts')
<script>
    const slider = document.getElementById('numQuestions');
    const numDisplay = document.getElementById('numDisplay');
    const maxLabel = document.getElementById('maxLabel');
    const sourceInputs = document.querySelectorAll('input[name="source"]');
    const difficultyInputs = document.querySelectorAll('input[name="difficulty"]');
    const previewBox = document.getElementById('previewBox');

    const onlineGrid = document.getElementById('onlineCategoryGrid');
    const offlineGrid = document.getElementById('offlineCategoryGrid');
    const onlineBadge = document.getElementById('onlineBadge');
    const offlineBadge = document.getElementById('offlineBadge');

    const onlineIcons = {!! json_encode($onlineCatIcons) !!};
    const offlineIcons = {!! json_encode($offlineCatIcons) !!};

    const diffColors = { 'easy': '#10b981', 'medium': '#f59e0b', 'hard': '#ef4444' };
    const diffLabels = { 'easy': 'Easy', 'medium': 'Medium', 'hard': 'Hard' };
    const diffIconNames = { 'easy': 'wifi', 'medium': 'wifi', 'hard': 'wifi' };

    sourceInputs.forEach(input => {
        input.addEventListener('change', function() {
            const isOnline = this.value === 'online';
            onlineGrid.classList.toggle('hidden', !isOnline);
            offlineGrid.classList.toggle('hidden', isOnline);
            onlineBadge.style.display = isOnline ? 'flex' : 'none';
            offlineBadge.style.display = isOnline ? 'none' : 'flex';
            document.querySelectorAll('input[name="category"]').forEach(c => c.checked = false);
            slider.max = isOnline ? 50 : 20;
            maxLabel.textContent = isOnline ? '50' : '20';
            if (!isOnline && parseInt(slider.value) > 20) { slider.value = 20; numDisplay.textContent = '20'; }
            updatePreview();
            lucide.createIcons();
        });
    });

    slider.addEventListener('input', function() { numDisplay.textContent = this.value; updatePreview(); });

    document.addEventListener('change', function(e) {
        if (e.target.name === 'category') {
            const max = parseInt(e.target.dataset.max);
            slider.max = max; maxLabel.textContent = max;
            if (parseInt(slider.value) > max) { slider.value = max; numDisplay.textContent = max; }
            updatePreview();
        }
    });

    difficultyInputs.forEach(input => input.addEventListener('change', updatePreview));

    function updatePreview() {
        const selCat = document.querySelector('input[name="category"]:checked');
        const selDiff = document.querySelector('input[name="difficulty"]:checked');
        const selSrc = document.querySelector('input[name="source"]:checked');

        if (selCat) {
            previewBox.classList.remove('hidden');
            const isOnline = selSrc && selSrc.value === 'online';

            // Source
            const srcIcon = document.getElementById('previewSourceIcon');
            srcIcon.setAttribute('data-lucide', isOnline ? 'wifi' : 'package');
            document.getElementById('previewSource').textContent = isOnline ? 'Online' : 'Offline';

            // Category
            const allIcons = isOnline ? onlineIcons : offlineIcons;
            const catIcon = document.getElementById('previewCatIcon');
            catIcon.setAttribute('data-lucide', allIcons[selCat.value] || 'book-open');
            document.getElementById('previewCat').textContent = selCat.value;

            document.getElementById('previewNum').textContent = slider.value;

            if (selDiff) {
                document.getElementById('previewDiffDot').style.background = diffColors[selDiff.value];
                document.getElementById('previewDiff').textContent = diffLabels[selDiff.value];
            }

            lucide.createIcons();
        }
    }

    let generateConfirmed = false;
    document.getElementById('generateForm').addEventListener('submit', function(e) {
        const selCat = document.querySelector('input[name="category"]:checked');
        const selDiff = document.querySelector('input[name="difficulty"]:checked');
        const selSrc = document.querySelector('input[name="source"]:checked');

        if (!selCat) {
            e.preventDefault();
            showAlert('target', 'amber', 'Pick a Category', 'Please select a category before generating your quiz.', 'OK', 'modal-btn-warning');
            return;
        }

        if (!generateConfirmed) {
            e.preventDefault();
            const isOnline = selSrc && selSrc.value === 'online';
            const sourceLabel = isOnline ? 'Online' : 'Offline';

            showConfirm('zap', 'purple', 'Generate Quiz?',
                `<strong>${selCat.value}</strong> — ${slider.value} questions (${selDiff ? diffLabels[selDiff.value] : 'Medium'})<br>Source: ${sourceLabel}<br><br>A new quiz will be created for you!`,
                'Generate', 'modal-btn-success',
                function() { generateConfirmed = true; document.getElementById('generateForm').submit(); }
            );
            return;
        }

        const btn = document.getElementById('generateBtn');
        const isOnline = selSrc && selSrc.value === 'online';
        btn.innerHTML = '<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> <span>' + (isOnline ? 'Fetching questions...' : 'Generating quiz...') + '</span>';
        btn.disabled = true;
    });

    updatePreview();
</script>
@endpush
@endsection
