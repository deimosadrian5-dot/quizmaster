@extends('layouts.app')

@section('title', 'Results: ' . $quiz->title)

@section('content')
<div class="max-w-3xl mx-auto px-4 py-12">

    <!-- Score Summary -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden fade-in mb-8">
        <div class="gradient-bg p-8 text-white text-center">
            <div class="w-16 h-16 bg-white/15 rounded-2xl flex items-center justify-center mx-auto mb-3 bounce-in">
                <i data-lucide="{{ $attempt->grade['icon'] }}" class="w-8 h-8"></i>
            </div>
            <h1 class="text-3xl font-extrabold mb-2">{{ $attempt->grade['label'] }}</h1>
            <p class="text-indigo-200">{{ $attempt->player_name }}'s Results</p>
        </div>

        <div class="p-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="text-center p-4 bg-indigo-50 rounded-xl border border-indigo-100">
                    <div class="text-2xl font-extrabold text-indigo-700">{{ $attempt->score }}</div>
                    <div class="text-xs text-gray-500 mt-1">Points Earned</div>
                </div>
                <div class="text-center p-4 bg-blue-50 rounded-xl border border-blue-100">
                    <div class="text-2xl font-extrabold text-blue-700">{{ $attempt->percentage }}%</div>
                    <div class="text-xs text-gray-500 mt-1">Accuracy</div>
                </div>
                <div class="text-center p-4 bg-emerald-50 rounded-xl border border-emerald-100">
                    <div class="text-2xl font-extrabold text-emerald-700">{{ $attempt->correct_answers }}/{{ $attempt->total_questions }}</div>
                    <div class="text-xs text-gray-500 mt-1">Correct</div>
                </div>
                <div class="text-center p-4 bg-amber-50 rounded-xl border border-amber-100">
                    <div class="text-2xl font-extrabold text-amber-700">{{ $attempt->time_taken }}s</div>
                    <div class="text-xs text-gray-500 mt-1">Time Taken</div>
                </div>
            </div>

            <!-- Score Bar -->
            <div class="mb-6">
                <div class="flex justify-between text-sm text-gray-500 mb-1">
                    <span>Score Progress</span>
                    <span>{{ $attempt->score }}/{{ $attempt->total_points }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="gradient-bg h-3 rounded-full transition-all duration-1000" style="width: {{ $attempt->percentage }}%"></div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('quizzes.play', $quiz) }}"
                   class="inline-flex items-center justify-center gap-2 gradient-bg text-white px-6 py-3 rounded-xl font-bold text-center hover:opacity-90 transition text-sm">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i> Try Again
                </a>
                <a href="{{ route('quizzes.index') }}"
                   class="inline-flex items-center justify-center gap-2 bg-gray-100 text-gray-700 px-6 py-3 rounded-xl font-bold text-center hover:bg-gray-200 transition text-sm border border-gray-200">
                    <i data-lucide="layout-grid" class="w-4 h-4"></i> All Quizzes
                </a>
                <a href="{{ route('quizzes.leaderboard', ['quiz' => $quiz->id]) }}"
                   class="inline-flex items-center justify-center gap-2 bg-amber-50 text-amber-800 px-6 py-3 rounded-xl font-bold text-center hover:bg-amber-100 transition text-sm border border-amber-200">
                    <i data-lucide="trophy" class="w-4 h-4"></i> Leaderboard
                </a>
            </div>
        </div>
    </div>

    <!-- Question Review -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 fade-in">
        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
            <i data-lucide="clipboard-list" class="w-5 h-5 text-indigo-500"></i> Question Review
        </h2>

        <div class="space-y-5">
            @foreach($results as $index => $result)
            <div class="border rounded-xl overflow-hidden {{ $result['is_correct'] ? 'border-emerald-200' : 'border-red-200' }}">
                <div class="p-4 {{ $result['is_correct'] ? 'bg-emerald-50' : 'bg-red-50' }}">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <span class="inline-flex items-center gap-1 text-sm font-medium {{ $result['is_correct'] ? 'text-emerald-700' : 'text-red-700' }}">
                                @if($result['is_correct'])
                                    <i data-lucide="check-circle" class="w-4 h-4"></i> Correct
                                @else
                                    <i data-lucide="x-circle" class="w-4 h-4"></i> Incorrect
                                @endif
                                · {{ $result['points'] }} pts
                            </span>
                            <h4 class="font-bold text-gray-800 mt-1 text-sm">Q{{ $index + 1 }}. {{ $result['question'] }}</h4>
                        </div>
                    </div>
                </div>
                <div class="p-4 space-y-2">
                    @foreach($result['options'] as $key => $optionText)
                    <div class="flex items-center p-2 rounded-lg text-sm
                        @if($key === $result['correct_answer']) bg-emerald-50 text-emerald-800 font-medium border border-emerald-200
                        @elseif($key === $result['user_answer'] && !$result['is_correct']) bg-red-50 text-red-800 border border-red-200
                        @else text-gray-600 border border-transparent
                        @endif">
                        <span class="w-7 h-7 rounded-md {{ $key === $result['correct_answer'] ? 'bg-emerald-500 text-white' : 'bg-gray-200' }} flex items-center justify-center text-xs font-bold mr-3">
                            {{ strtoupper($key) }}
                        </span>
                        {{ $optionText }}
                        @if($key === $result['correct_answer'])
                            <span class="ml-auto"><i data-lucide="check" class="w-4 h-4 text-emerald-600"></i></span>
                        @elseif($key === $result['user_answer'] && !$result['is_correct'])
                            <span class="ml-auto"><i data-lucide="x" class="w-4 h-4 text-red-600"></i></span>
                        @endif
                    </div>
                    @endforeach

                    @if($result['explanation'])
                    <div class="mt-2 p-3 bg-blue-50 rounded-lg text-sm text-blue-800 flex items-start gap-2 border border-blue-100">
                        <i data-lucide="lightbulb" class="w-4 h-4 mt-0.5 flex-shrink-0"></i>
                        <span>{{ $result['explanation'] }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script>
    @if($attempt->percentage >= 70)
    const cs = getComputedStyle(document.documentElement);
    const colors = [cs.getPropertyValue('--t-primary').trim(), cs.getPropertyValue('--t-secondary').trim(), '#f59e0b', '#10b981', '#ef4444', '#3b82f6'];
    for (let i = 0; i < 50; i++) {
        setTimeout(() => {
            const c = document.createElement('div');
            c.className = 'confetti';
            c.style.left = Math.random() * 100 + 'vw';
            c.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            c.style.borderRadius = Math.random() > 0.5 ? '50%' : '2px';
            c.style.width = (Math.random() * 8 + 4) + 'px';
            c.style.height = (Math.random() * 8 + 4) + 'px';
            c.style.animationDuration = (Math.random() * 2 + 2) + 's';
            document.body.appendChild(c);
            setTimeout(() => c.remove(), 4000);
        }, i * 60);
    }
    @endif
</script>
@endpush
@endsection
