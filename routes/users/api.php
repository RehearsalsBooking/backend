<?php

use App\Http\Controllers\Users\OrganizationsController;

Route::get('organizations', [OrganizationsController::class, 'index']);
