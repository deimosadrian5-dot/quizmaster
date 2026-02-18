@extends('layouts.app')

@section('title', $quiz->title)

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12">
    <!-- Quiz Hero -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden fade-in">
        <div class="gradient-bg p-10 text-white text-center relative">
            <div class="w-16 h-16 bg-white/15 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i data-lucide="{{ $quiz->category_icon }}" class="w-8 h-8"></i>
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold mb-2">{{ $quiz->title }}</h1>
            <p class="text-indigo-200 text-lg max-w-lg mx-auto">{{ $quiz->description }}</p>
        </div>

        <div class="p-8">
            <!-- Stats Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="text-center p-4 bg-indigo-50 rounded-xl border border-indigo-100">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                        <i data-lucide="file-text" class="w-5 h-5 text-indigo-600"></i>
                    </div>
                    <div class="text-2xl font-bold text-indigo-700">{{ $quiz->questions_count }}</div>
                    <div class="text-xs text-gray-500">Questions</div>
                </div>
                <div class="text-center p-4 bg-blue-50 rounded-xl border border-blue-100">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                        <i data-lucide="clock" class="w-5 h-5 text-blue-600"></i>
                    </div>
                    <div class="text-2xl font-bold text-blue-700">{{ $quiz->time_per_question }}s</div>
                    <div class="text-xs text-gray-500">Per Question</div>
                </div>
                <div class="text-center p-4 bg-{{ $quiz->difficulty_color }}-50 rounded-xl border border-{{ $quiz->difficulty_color }}-100">
                    <div class="w-10 h-10 bg-{{ $quiz->difficulty_color }}-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                        <i data-lucide="gauge" class="w-5 h-5 text-{{ $quiz->difficulty_color }}-600"></i>
                    </div>
                    <div class="text-2xl font-bold text-{{ $quiz->difficulty_color }}-700 capitalize">{{ $quiz->difficulty }}</div>
                    <div class="text-xs text-gray-500">Difficulty</div>
                </div>
                <div class="text-center p-4 bg-amber-50 rounded-xl border border-amber-100">
                    <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                        <i data-lucide="users" class="w-5 h-5 text-amber-600"></i>
                    </div>
                    <div class="text-2xl font-bold text-amber-700">{{ $quiz->attempts_count }}</div>
                    <div class="text-xs text-gray-500">Attempts</div>
                </div>
            </div>

            <!-- Start Button -->
            <div class="text-center">
                <button onclick="confirmStart()"
                   class="inline-flex items-center gap-2 gradient-bg text-white px-10 py-4 rounded-xl font-bold text-lg hover:opacity-90 transition pulse-glow cursor-pointer shadow-lg">
                    <i data-lucide="play" class="w-5 h-5"></i> Start Quiz Now
                </button>
                <p class="text-gray-400 text-sm mt-3">Good luck! You've got this.</p>
            </div>
        </div>
    </div>

    <!-- Top Scores -->
    @if($topScores->isNotEmpty())
    <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-100 p-8 fade-in">
        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
            <i data-lucide="trophy" class="w-5 h-5 text-amber-500"></i> Top Scores
        </h2>
        <div class="space-y-3">
            @foreach($topScores as $index => $attempt)
            <div class="flex items-center justify-between p-4 rounded-xl {{ $index === 0 ? 'bg-amber-50 border border-amber-200' : 'bg-gray-50 border border-gray-100' }}">
                <div class="flex items-center gap-4">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold
                        {{ $index === 0 ? 'bg-amber-400 text-white' : ($index === 1 ? 'bg-gray-300 text-white' : ($index === 2 ? 'bg-orange-400 text-white' : 'bg-gray-200 text-gray-500')) }}">
                        {{ $index + 1 }}
                    </div>
                    <div>
                        <div class="font-semibold text-gray-800">{{ $attempt->player_name }}</div>
                        <div class="text-xs text-gray-400">{{ $attempt->created_at->diffForHumans() }}</div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-lg font-bold text-indigo-700">{{ $attempt->score }}/{{ $attempt->total_points }}</div>
                    <div class="text-xs text-gray-400 flex items-center gap-1 justify-end">
                        <i data-lucide="clock" class="w-3 h-3"></i> {{ $attempt->time_taken }}s
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="text-center mt-8">
        <a href="{{ route('quizzes.index') }}" class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-800 font-medium text-sm">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to All Quizzes
        </a>
    </div>
</div>

@push('scripts')
<script>
    function confirmStart() {
        showConfirm(
            'play', 'green', 'Ready to Start?',
            '<strong>{{ $quiz->title }}</strong><br><br>' +
            '{{ $quiz->questions_count }} questions<br>' +
            '{{ $quiz->time_per_question }}s per question<br>' +
            '{{ ucfirst($quiz->difficulty) }} difficulty<br><br>' +
            'Once you start, the timer begins immediately!',
            "Let's Go!", 'modal-btn-success',
            function() { window.location.href = "{{ route('quizzes.play', $quiz) }}"; }
        );
    }
</script>
@endpush
@endsection
