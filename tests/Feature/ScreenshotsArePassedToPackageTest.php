<?php

namespace Tests\Feature;

use App\Collaborator;
use App\Package;
use App\Screenshot;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ScreenshotsArePassedToPackageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_packages_screenshots_are_passed_to_the_view()
    {
        $packageNamespace = 'tightenco';
        $packageName = 'bae';
        $user = factory(User::class)->create();
        $collaborator = factory(Collaborator::class)->make();
        $user->collaborators()->save($collaborator);
        $packageA = factory(Package::class)->make([
            'composer_name' => "{$packageNamespace}/{$packageName}",
        ]);
        $collaborator->authoredPackages()->save($packageA);
        $screenshotA = factory(Screenshot::class)->create(['uploader_id' => $user->id]);
        $packageA->screenshots()->save($screenshotA);
        $packageB = factory(Package::class)->create();
        $screenshotB = factory(Screenshot::class)->create(['uploader_id' => $user->id]);
        $packageB->screenshots()->save($screenshotB);

        $response = $this->actingAs($user)
            ->get(route('packages.show', ['namespace' => $packageNamespace, 'name' => $packageName]));
        $response->assertSuccessful();

        $this->assertCount(1, $response->viewData('screenshots'));
        $this->assertTrue($response->viewData('screenshots')->contains($screenshotA));
        $this->assertFalse($response->viewData('screenshots')->contains($screenshotB));
    }
}
