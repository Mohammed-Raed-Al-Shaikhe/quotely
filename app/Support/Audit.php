<?php
namespace App\Support;
use App\Models\AuditLog;
class Audit {public static function record(string $event,object $subject,array $metadata=[]):void{$user=auth()->user();$companyId=$user?->company_id??$subject->company_id??null;if(!$companyId)return;AuditLog::create(['company_id'=>$companyId,'user_id'=>$user?->id,'event'=>$event,'subject_type'=>$subject::class,'subject_id'=>$subject->id,'metadata'=>$metadata?:null,'ip_address'=>request()->ip()]);}}
