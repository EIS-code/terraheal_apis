<?php

namespace App\Jobs;

use App\User;
use Carbon\Carbon;

class UserNotification extends BaseNotification
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $id, string $title, string $description, int $sendTo, int $sendFrom)
    {
        $deviceToken = User::getToken($id);

        $dataPayload = ["date" => Carbon::now()->timestamp * 1000];

        parent::__construct($deviceToken, $title, $description, $dataPayload, $sendTo, $sendFrom, $id);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->send();
    }
}
