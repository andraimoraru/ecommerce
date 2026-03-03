<?php
declare(strict_types=1);

namespace App\Controllers\Account;

use App\Core\Controller;

final class Dashboard extends Controller
{
    public function index(): void
    {
        $this->view('account/index', [
            'title' => 'My Account',
        ]);
    }
}