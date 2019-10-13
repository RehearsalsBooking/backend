<?php

namespace Tests\Feature\Rehearsals;

use App\Http\Resources\Users\RehearsalResource;
use App\Models\Rehearsal;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RehearsalsFilterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_filter_rehearsals_of_organization_by_date_range(): void
    {
        $organization = $this->createOrganization();

        $rehearsal9to11 = factory(Rehearsal::class)->create([
            'organization_id' => $organization->id,
            'starts_at' => $this->getDateTimeAt(9, 0),
            'ends_at' => $this->getDateTimeAt(11, 0),
        ]);

        $rehearsal12to14 = factory(Rehearsal::class)->create([
            'organization_id' => $organization->id,
            'starts_at' => $this->getDateTimeAt(12, 0),
            'ends_at' => $this->getDateTimeAt(14, 0),
        ]);

        $rehearsal16to18 = factory(Rehearsal::class)->create([
            'organization_id' => $organization->id,
            'starts_at' => $this->getDateTimeAt(16, 0),
            'ends_at' => $this->getDateTimeAt(18, 0),
        ]);

        $response = $this->json('get', route('rehearsals.list'), [
            'organization_id' => $organization->id,
            'from' => $this->getDateTimeAt(13, 00)
        ]);
        $response->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['data']);
        $this->assertEquals(
            RehearsalResource::collection(collect([$rehearsal16to18]))->response()->getData(true),
            $data
        );

        $response = $this->json('get', route('rehearsals.list'), [
            'organization_id' => $organization->id,
            'to' => $this->getDateTimeAt(13, 00)
        ]);
        $response->assertOk();
        $data = $response->json();
        $this->assertCount(2, $data['data']);
        $this->assertEquals(
            RehearsalResource::collection(collect([$rehearsal9to11, $rehearsal12to14]))->response()->getData(true),
            $data
        );

        $response = $this->json('get', route('rehearsals.list'), [
            'organization_id' => $organization->id,
            'from' => $this->getDateTimeAt(11, 30),
            'to' => $this->getDateTimeAt(15, 00)
        ]);
        $response->assertOk();
        $data = $response->json();
        $this->assertCount(1, $data['data']);
        $this->assertEquals(
            RehearsalResource::collection(collect([$rehearsal12to14]))->response()->getData(true),
            $data
        );
    }

    /**
     * @test
     * @dataProvider invalidFilterData
     * @param $data
     * @param $invalidKey
     */
    public function it_responds_with_422_when_user_provided_invalid_data_for_filter($data, $invalidKey): void
    {
        $organization = $this->createOrganization();
        $response = $this->json(
            'get',
            route('rehearsals.list'),
            array_merge($data, ['organization_id' => $organization->id])
        );

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors($invalidKey);
    }

    /**
     * @return array
     */
    public function invalidFilterData(): array
    {
        return [
            [
                [
                    'from' => 'invalid date',
                ],
                'from'
            ],
            [
                [
                    'to' => 'invalid date',
                ],
                'to'
            ],
            [
                [
                    'from' => Carbon::now()->addDay(),
                    'to' => Carbon::now()->addDay()->subHour(),
                ],
                'to'
            ],
        ];
    }
}
