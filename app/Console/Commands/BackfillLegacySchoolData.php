<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
class BackfillLegacySchoolData extends Command {
 protected $signature='schools:backfill {school : Target school ID} {--commit : Persist changes; otherwise dry run}';
 protected $description='Safely assign legacy null school_id records to one verified school';
 private $tables=['users','my_classes','sections','subjects','student_records','dorms','exams','marks','grades','pins','skills','exam_records','books','book_requests','staff_records','payments','payment_records','receipts','time_table_records','time_slots','time_tables','promotions','settings'];
 public function handle(){
  $schoolId=(int)$this->argument('school'); if(!DB::table('schools')->where('id',$schoolId)->exists()){ $this->error('School not found.'); return 1; }
  $counts=[]; foreach($this->tables as $table){$counts[$table]=DB::table($table)->whereNull('school_id')->count();}
  $this->table(['Table','Unassigned records'],collect($counts)->map(fn($n,$t)=>[$t,$n])->values()->all());
  if(!$this->option('commit')){$this->warn('Dry run only. Re-run with --commit after verifying the target school.');return 0;}
  if(!$this->confirm('Assign every listed legacy record to school '.$schoolId.'?')) return 1;
  DB::transaction(function()use($schoolId){foreach($this->tables as $table)DB::table($table)->whereNull('school_id')->update(['school_id'=>$schoolId]);});
  $this->info('Backfill completed. Verify tenant tests before the non-null cutover.'); return 0;
 }
}
