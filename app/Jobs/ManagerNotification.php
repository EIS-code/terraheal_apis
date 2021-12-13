<?php

namespace App\Jobs;

use App\Notification as modalNotification;
use App\Manager;
use Carbon\Carbon;

class ManagerNotification extends BaseNotification
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $id, string $title, string $description, int $sendTo, int $sendFrom)
    {
        $deviceToken = Manager::getToken($id);

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
