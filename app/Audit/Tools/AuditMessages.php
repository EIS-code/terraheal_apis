<?php

namespace App\Audit\Tools;

use App\Role;
use App\Client;
use App\ClientPurposeArticle;
use App\Permission;
use App\ClientFee;
use App\DeletedRecord;
use App\Account;
use App\ClientCondition;
use App\ClientPrivateInformation;
use App\ClientDocument;
use App\ClientEmailProgressReport;
use App\ClientTermsAndCondition;
use App\FollowUp;

class AuditMessages 
{
    
    /**
     * Get readable messages for audits
     *
     * @param object $audits
     * @return object $audits
     */
    static public function get($audits){
        foreach ($audits as $key => $value) {
            $audits[$key]['event_message'] = self::getMessage($value);
        }

        return $audits;
    }

    /**
     * Get specified messages for an audit
     *
     * @param object $audit
     * @return string $message
     */
    static public function getMessage($audit){
        $message = "";
        
		

        return $message;
    }

    /**
     * Get message for created event
     *
     * @param object $audit
     * @return string $message
     */
    static private function getMessageForCreated($audit){
        $message     = "";
        
		

        return $message;
    }

    /**
     * Get message for updated event
     *
     * @param object $audit
     * @return string $message
     */
    static private function getMessageForUpdated($audit){
        $message     = "";
        
		

        return $message;
    }

    /**
     * Get message for deleted event
     *
     * @param object $audit
     * @return string $message
     */
    static private function getMessageForDeleted($audit){
        $message     = "";
        
		

        return $message;
    }
}