<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'player_name',
        'score',
        'total_points',
        'correct_answers',
        'total_questions',
        'time_taken',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function getPercentageAttribute()
    {
        if ($this->total_points == 0) return 0;
        return round(($this->score / $this->total_points) * 100);
    }

    public function getGradeAttribute()
    {
        $pct = $this->percentage;
        if ($pct >= 90) return ['label' => 'Genius!', 'emoji' => '🏆', 'icon' => 'crown', 'color' => 'yellow'];
        if ($pct >= 70) return ['label' => 'Great Job!', 'emoji' => '🌟', 'icon' => 'thumbs-up', 'color' => 'green'];
        if ($pct >= 50) return ['label' => 'Not Bad!', 'emoji' => '👍', 'icon' => 'smile', 'color' => 'blue'];
        if ($pct >= 30) return ['label' => 'Keep Trying!', 'emoji' => '💪', 'icon' => 'trending-up', 'color' => 'orange'];
        return ['label' => 'Better Luck Next Time!', 'emoji' => '😅', 'icon' => 'refresh-cw', 'color' => 'red'];
    }
}
