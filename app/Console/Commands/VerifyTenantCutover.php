<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
class VerifyTenantCutover extends Command {
 protected $signature='schools:verify-cutover'; protected $description='Verify that tenant-owned tables are ready for non-null school_id cutover';
 private $tables=['users','my_classes','sections','subjects','student_records','dorms','exams','marks','grades','pins','skills','exam_records','books','book_requests','staff_records','payments','payment_records','receipts','time_table_records','time_slots','time_tables','promotions','settings'];
 public function handle(){ $rows=[];$blocked=false;foreach($this->tables as $t){$n=DB::table($t)->whereNull('school_id')->count();$rows[]=[$t,$n,$n?'BLOCKED':'READY'];$blocked=$blocked||$n>0;}$this->table(['Table','Null school_id','Status'],$rows);if($blocked){$this->error('Cutover blocked. Backfill and verify these records first.');return 1;}$this->info('All tenant records are assigned. Run staging E2E tests before applying a non-null schema migration.');return 0; }
}
