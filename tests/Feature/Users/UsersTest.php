<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsersTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private array $credentials = [
        'email' => 'some@email.com',
        'password' => 'some password',
    ];

    /** @test */
    public function user_gets_correct_info_about_his_bands_when_he_fetches_info_about_himself(): void
    {
        $adminnedBand = $this->createBandForUser($this->user);
        $participatingBand = $this->createBand();
        $participatingBand->addMember($this->user->id);

        $this->assertEquals(2, $this->user->bands()->count());

        $this->actingAs($this->user);

        $response = $this->json('get', route('me'));

        $response->assertOk();

        $fetchedData = $response->json('data');

        $this->assertArrayHasKey('bands', $fetchedData);

        $fetchedBands = $fetchedData['bands'];

        $this->assertCount(2, $fetchedBands);

        $usersBands = collect([$adminnedBand, $participatingBand])->toArray();

        $comparingFunction = static function ($a, $b) {
            if ($a['id'] === $b['id']) {
                return 0;
            }

            return ($a['id'] < $b['id']) ? -1 : 1;
        };
        usort($fetchedBands, $comparingFunction);
        usort($usersBands, $comparingFunction);

        foreach ($fetchedBands as $index => $fetchedBand) {
            $this->assertEquals($usersBands[$index]['id'], $fetchedBand['id']);
            if ($usersBands[$index]['admin_id'] === $this->user->id) {
                $this->assertTrue($fetchedBand['is_admin']);
            } else {
                $this->assertFalse($fetchedBand['is_admin']);
            }
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create([
            'password' => bcrypt($this->credentials['password']),
            'email' => $this->credentials['email'],
        ]);
    }
}
