<?php

namespace Tests\Feature\Management\Prices;

use App\Models\Organization\Organization;
use App\Models\Organization\OrganizationPrice;
use Illuminate\Http\Response;
use Tests\Feature\Management\ManagementTestCase;

class UpdatePriceTest extends ManagementTestCase
{
    private string $endpoint = 'management.organizations.prices.update';
    private string $httpVerb = 'put';

    /** @test */
    public function unauthorized_user_cannot_access_endpoint(): void
    {
        $this->json($this->httpVerb, route($this->endpoint, [1, 1]))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function it_responds_with_forbidden_error_when_endpoint_is_accessed_not_by_organization_owner(): void
    {
        $ordinaryClient = $this->createUser();

        $managerOfAnotherOrganization = $this->createUser();
        $this->createOrganizationForUser($managerOfAnotherOrganization);

        $priceId = $this->organization->prices()->first()->id;

        $this->actingAs($ordinaryClient);

        $this->json(
            $this->httpVerb,
            route($this->endpoint, [$this->organization->id, $priceId]),
            ['price' => 1]
        )
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->actingAs($managerOfAnotherOrganization);
        $this->json(
            $this->httpVerb,
            route($this->endpoint, [$this->organization->id, $priceId]),
            ['price' => 1]
        )
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function updated_price_should_be_owned_by_organization(): void
    {
        $managerOfAnotherOrganization = $this->createUser();
        $anotherOrganization = $this->createOrganizationForUser($managerOfAnotherOrganization);

        $priceIdToDelete = $this->organization->prices()->first()->id;

        $this->actingAs($managerOfAnotherOrganization);
        $this->json(
            $this->httpVerb,
            route($this->endpoint, [$anotherOrganization->id, $priceIdToDelete])
        )
            ->assertNotFound();
    }

    /** @test */
    public function it_responds_with_404_when_unknown_price_or_organization_is_given(): void
    {
        $priceId = $this->organization->prices()->where('day', 1)->first()->id;

        $this->actingAs($this->manager);

        $unknownPriceId = 1000;
        $unknownOrganizationId = 1000;

        $this->assertEquals(0, OrganizationPrice::where('id', $unknownPriceId)->count());
        $this->assertEquals(0, Organization::where('id', $unknownOrganizationId)->count());

        $this->json($this->httpVerb, route($this->endpoint, [$this->organization->id, $unknownPriceId]))
            ->assertStatus(Response::HTTP_NOT_FOUND);
        $this->json($this->httpVerb, route($this->endpoint, [$unknownOrganizationId, $priceId]))
            ->assertStatus(Response::HTTP_NOT_FOUND);
        $this->json($this->httpVerb, route($this->endpoint, [$this->organization->id, 'some text']))
            ->assertStatus(Response::HTTP_NOT_FOUND);
        $this->json($this->httpVerb, route($this->endpoint, ['some text', $priceId]))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function it_requires_correct_new_price(): void
    {
        $price = $this->organization->prices()->where('day', 1)->first()->id;

        $endpoint = route($this->endpoint, [$this->organization->id, $price]);

        $this->actingAs($this->manager);

        $this->json($this->httpVerb, $endpoint)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('price');

        $this->json(
            $this->httpVerb,
            $endpoint,
            ['price' => 'new price']
        )
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('price');

        $this->json(
            $this->httpVerb,
            $endpoint,
            ['price' => -100]
        )
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors('price');
    }

    /** @test */
    public function manager_of_organization_can_update_price_entry_of_his_organization(): void
    {
        $this->assertEquals(5, $this->organization->prices()->count());

        $price = $this->organization->prices()->where('day', 1)->first();

        $newPrice = $price->price + 100;

        $this->actingAs($this->manager);

        $response = $this->json(
            $this->httpVerb,
            route($this->endpoint, [$this->organization->id, $price->id]),
            ['price' => $newPrice]
        );
        $response->assertOk();

        $this->assertEquals(5, $this->organization->prices()->count());
        $this->assertDatabaseMissing('organization_prices', [
            'day' => 1,
            'organization_id' => $this->organization->id,
            'price' => $price->price
        ]);
        $this->assertEquals($newPrice, $price->fresh()->price);
    }
}
