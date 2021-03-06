<?php

namespace Tests\Feature\Rehearsals\Booking;

use App\Models\Rehearsal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class RehearsalBookingValidationTest extends TestCase
{
    use RefreshDatabase;
    use ValidatesRehearsalTime;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->createUser());
    }

    /** @test */
    public function it_responds_with_422_when_user_provided_unknown_organization_id(): void
    {
        $this->json('post', route('rehearsals.create'), [
            'organization_id' => 10000,
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('organization_id');

        $this->json('post', route('rehearsals.create'), [
            'organization_id' => 'asd',
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('organization_id');
    }

    /** @test */
    public function it_responds_with_validation_error_when_user_provided_unknown_band_id(): void
    {
        $organization = $this->createOrganization();

        $this->json('post', route('rehearsals.create'), [
            'organization_id' => $organization->id,
            'band_id' => 10000,
            'starts_at' => $this->getDateTimeAt(12, 00),
            'ends_at' => $this->getDateTimeAt(13, 00),
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('band_id');
    }

    /**
     * @test
     * @dataProvider getDataWithInvalidFormat
     * @param $data
     * @param $keyWithError
     */
    public function it_responds_with_validation_error_when_user_provided_invalid_time_parameters(
        $data,
        $keyWithError
    ): void {
        $organization = $this->createOrganization();

        $response = $this->json(
            'post',
            route('rehearsals.create'),
            array_merge($data, ['organization_id' => $organization->id])
        );

        $response->assertJsonValidationErrors($keyWithError);
    }

    /**
     * @test
     */
    public function it_responds_with_validation_error_when_user_provided_time_when_organization_is_closed(): void
    {
        $organization = $this->createOrganization();
        $this->performTestWhenOrganizationIsClosed(
            'post',
            route('rehearsals.create'),
            $organization,
            ['organization_id' => $organization->id]
        );
        $this->assertEquals(0, $organization->rehearsals()->count());
    }

    /**
     * @test
     */
    public function it_responds_with_validation_error_when_user_provided_incorrect_rehearsal_duration(): void
    {
        $organization = $this->createOrganization();
        $this->performTestsWhenUserProvidedIncorrectRehearsalDuration(
            'post',
            route('rehearsals.create'),
            $organization,
            ['organization_id' => $organization->id]
        );
        $this->assertEquals(0, $organization->rehearsals()->count());
    }

    /**
     * @test
     */
    public function it_responds_with_validation_error_when_user_tries_to_book_rehearsal_longer_than_24_hours(): void
    {
        $organization = $this->createOrganization();

        $this->performTestsWhenUserProvidesRehearsalTimeLongerThan24Hours(
            'post',
            route('rehearsals.create'),
            $organization,
            ['organization_id' => $organization->id]
        );
        $this->assertEquals(0, $organization->rehearsals()->count());
    }

    /** @test */
    public function it_responds_with_validation_error_when_user_selected_unavailable_time(): void
    {
        $organization = $this->createOrganization();

        $this->performTestsWhenUserSelectedUnavailableTime(
            'post',
            route('rehearsals.create'),
            $organization,
            ['organization_id' => $organization->id]
        );

        $this->assertEquals(3, Rehearsal::count());

        $this->json(
            'post',
            route('rehearsals.create'),
            [
                'organization_id' => $organization->id,
                'starts_at' => $this->getDateTimeAt(11, 00),
                'ends_at' => $this->getDateTimeAt(12, 00),
            ]
        )->assertStatus(Response::HTTP_CREATED);

        $this->assertEquals(4, Rehearsal::count());
    }
}
