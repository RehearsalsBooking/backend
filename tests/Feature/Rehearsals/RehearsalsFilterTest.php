<?php

namespace Tests\Feature\Rehearsals;

use App\Http\Resources\Users\RehearsalResource;
use App\Models\Band;
use App\Models\Organization\Organization;
use App\Models\Rehearsal;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class RehearsalsFilterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var Organization
     */
    private Organization $organization;

    /** @test */
    public function user_can_fetch_rehearsals_of_organization(): void
    {
        $rehearsals = $this->createRehearsalsForOrganization($this->organization, 5);
        $this->createRehearsalsForOrganization($this->createOrganization(), 5);

        $this->assertEquals(10, Rehearsal::count());

        $response = $this->json(
            'get',
            route('rehearsals.list'),
            ['organization_id' => $this->organization->id]
        );
        $response->assertOk();

        $data = $response->json();

        $this->assertCount(5, $data['data']);
        $this->assertEquals(
            RehearsalResource::collection($rehearsals->sortBy('id'))->response()->getData(true),
            $data
        );
    }

    /** @test */
    public function it_responds_with_404_when_client_provided_unknown_organization(): void
    {
        $this
            ->json('get', route('rehearsals.list'), ['organization_id' => 'asd'])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('organization_id');

        $this
            ->json('get', route('rehearsals.list'), ['organization_id' => 10000])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('organization_id');
    }

    /** @test */
    public function user_can_fetch_rehearsals_of_band(): void
    {
        Band::truncate();
        $band = $this->createBand();
        $someOtherBand = $this->createBand();

        $this->assertEquals(2, Band::count());

        $bandsRehearsals = collect([
            $this->createRehearsalForBandInFuture($band),
            $this->createRehearsalForBandInThePast($band),
        ]);

        $this->createRehearsalForBandInFuture($someOtherBand);
        $this->createRehearsalForBandInThePast($someOtherBand);

        $this->assertEquals(4, Rehearsal::count());

        $response = $this->json('get', route('rehearsals.list'), [
            'band_id' => $band->id,
        ]);
        $response->assertOk();

        $data = $response->json();

        $this->assertCount(2, $data['data']);
        $this->assertEquals(
            RehearsalResource::collection($bandsRehearsals)->response()->getData(true),
            $data
        );
    }

    /** @test */
    public function it_responds_with_404_when_client_provided_unknown_band(): void
    {
        $this
            ->json('get', route('rehearsals.list'), ['band_id' => 'asd'])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('band_id');

        $this
            ->json('get', route('rehearsals.list'), ['band_id' => 10000])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('band_id');
    }

    /** @test */
    public function user_can_filter_rehearsals_of_organization_by_date_range(): void
    {
        $rehearsal9to11 = $this->createRehearsal(9, 11, $this->organization);

        $rehearsal12to14 = $this->createRehearsal(12, 14, $this->organization);

        $rehearsal16to18 = $this->createRehearsal(16, 18, $this->organization);

        $response = $this->json('get', route('rehearsals.list'), [
            'organization_id' => $this->organization->id,
            'from' => $this->getDateTimeAt(13, 00),
        ]);
        $response->assertOk();

        $data = $response->json();
        // 9----11  12----14  16----18
        //             13-------------
        $this->assertCount(1, $data['data']);
        $this->assertEquals(
            RehearsalResource::collection(collect([$rehearsal16to18]))->response()->getData(true),
            $data
        );

        $response = $this->json('get', route('rehearsals.list'), [
            'organization_id' => $this->organization->id,
            'from' => $this->getDateTimeAt(12, 00),
        ]);
        $response->assertOk();
        $data = $response->json();
        // 9----11  12----14  16----18
        //          12----------------
        $this->assertCount(2, $data['data']);
        $this->assertEquals(
            RehearsalResource::collection(collect([$rehearsal12to14, $rehearsal16to18]))->response()->getData(true),
            $data
        );

        $response = $this->json('get', route('rehearsals.list'), [
            'organization_id' => $this->organization->id,
            'to' => $this->getDateTimeAt(13, 00),
        ]);
        $response->assertOk();
        $data = $response->json();
        // 9----11  12----14  16----18
        // ------------13
        $this->assertCount(1, $data['data']);
        $this->assertEquals(
            RehearsalResource::collection(collect([$rehearsal9to11]))->response()->getData(true),
            $data
        );

        $response = $this->json('get', route('rehearsals.list'), [
            'organization_id' => $this->organization->id,
            'to' => $this->getDateTimeAt(14, 00),
        ]);
        $response->assertOk();
        $data = $response->json();
        // 9----11  12----14  16----18
        // ---------------14
        $this->assertCount(2, $data['data']);
        $this->assertEquals(
            RehearsalResource::collection(collect([$rehearsal9to11, $rehearsal12to14]))->response()->getData(true),
            $data
        );

        $response = $this->json('get', route('rehearsals.list'), [
            'organization_id' => $this->organization->id,
            'from' => $this->getDateTimeAt(11, 30),
            'to' => $this->getDateTimeAt(16, 00),
        ]);
        $response->assertOk();
        $data = $response->json();
        // 9----11  12----14  16----18
        //      11------------16
        $this->assertCount(1, $data['data']);
        $this->assertEquals(
            RehearsalResource::collection(collect([$rehearsal12to14]))->response()->getData(true),
            $data
        );

        $response = $this->json('get', route('rehearsals.list'), [
            'organization_id' => $this->organization->id,
            'from' => $this->getDateTimeAt(10, 30),
            'to' => $this->getDateTimeAt(17, 00),
        ]);
        $response->assertOk();
        $data = $response->json();
        // 9----11  12----14  16----18
        //   10------------------17
        $this->assertCount(1, $data['data']);
        $this->assertEquals(
            RehearsalResource::collection(collect([$rehearsal12to14]))->response()->getData(true),
            $data
        );
    }

    /**
     * @test
     * @dataProvider invalidFilterDataForDates
     * @param  array  $data
     * @param  string  $invalidKey
     */
    public function it_responds_with_422_when_user_provided_invalid_data_for_filter_by_date_filter(
        array $data,
        string $invalidKey
    ): void {
        $response = $this->json(
            'get',
            route('rehearsals.list'),
            array_merge($data, ['organization_id' => $this->organization->id])
        );

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors($invalidKey);
    }

    /**
     * @return array
     */
    public function invalidFilterDataForDates(): array
    {
        return [
            [
                [
                    'from' => 'invalid date',
                ],
                'from',
            ],
            [
                [
                    'to' => 'invalid date',
                ],
                'to',
            ],
            [
                [
                    'from' => Carbon::now()->addDay(),
                    'to' => Carbon::now()->addDay()->subHour(),
                ],
                'to',
            ],
        ];
    }

    /** @test */
    public function user_can_fetch_his_individual_rehearsals(): void
    {
        $john = $this->createUser();
        $max = $this->createUser();

        $this->createRehearsalForUser($john);
        $maxesRehearsal = $this->createRehearsalForUser($max);

        $this->assertEquals(2, Rehearsal::count());

        $response = $this->json('get', route('rehearsals.list'), [
            'user_id' => $max->id,
        ]);

        $response->assertOk();

        $maxesRehearsals = $response->json();

        $this->assertCount(1, $maxesRehearsals['data']);
        $this->assertEquals(
            RehearsalResource::collection(collect([$maxesRehearsal]))->response()->getData(true),
            $maxesRehearsals
        );
    }

    /** @test */
    public function when_client_fetches_rehearsals_of_user_he_also_receives_rehearsals_of_this_users_current_band(
    ): void
    {
        $max = $this->createUser();

        $maxesBand = $this->createBand();
        $maxesBand->addMember($max->id);

        $maxesOtherBand = $this->createBand();
        $maxesOtherBand->addMember($max->id);

        $rehearsalForMaxesBand = $this->createRehearsalForBandInFuture($maxesBand);
        $rehearsalForMaxesOtherBand = $this->createRehearsalForBandInFuture($maxesOtherBand);
        $rehearsalForMax = $this->createRehearsalForUser($max);
        $this->createRehearsalForUser($this->createUser());
        $this->createRehearsalForBandInFuture($this->createBand());

        $this->assertEquals(5, Rehearsal::count());

        $response = $this->json('get', route('rehearsals.list'), [
            'user_id' => $max->id,
        ]);

        $response->assertOk();

        $maxesRehearsals = $response->json();

        $this->assertCount(3, $maxesRehearsals['data']);
        $this->assertEquals(
            RehearsalResource::collection(collect([
                $rehearsalForMaxesBand, $rehearsalForMaxesOtherBand, $rehearsalForMax
            ]))->response()->getData(true),
            $maxesRehearsals
        );
    }

    /** @test */
    public function it_responds_with_404_when_client_provided_unknown_user(): void
    {
        $this
            ->json('get', route('rehearsals.list'), ['user_id' => 'asd'])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('user_id');

        $this
            ->json('get', route('rehearsals.list'), ['user_id' => 10000])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('user_id');
    }

    /** @test */
    public function it_limits_rehearsals(): void
    {
        $user = $this->createUser();
        $this->createRehearsalForUser($user);
        $this->createRehearsalForUser($user);
        $this->createRehearsalForUser($user);

        $limit = 2;

        $response = $this->json('get', route('rehearsals.list'), ['limit' => $limit]);
        $response->assertOk();

        $this->assertCount($limit, $response->json('data'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = $this->createOrganization();
    }
}
