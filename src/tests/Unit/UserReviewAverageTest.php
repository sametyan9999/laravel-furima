<?php

namespace Tests\Unit;

use App\Models\TradeReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserReviewAverageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function review_average_is_rounded()
    {
        $user = User::factory()->create();

        // 5, 4, 3 → 平均 4.0 → 四捨五入で 4
        TradeReview::factory()->create(['target_id' => $user->id, 'score' => 5]);
        TradeReview::factory()->create(['target_id' => $user->id, 'score' => 4]);
        TradeReview::factory()->create(['target_id' => $user->id, 'score' => 3]);

        $this->assertSame(4, $user->fresh()->getReviewAverage());
    }
}