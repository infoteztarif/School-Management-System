<?php
namespace Tests\Feature;
use App\Models\School;
use App\Models\Setting;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
class CrossTenantIsolationTest extends TestCase {
 use RefreshDatabase;
 public function test_school_users_cannot_read_or_create_settings_in_another_school(){
  $a=School::create(['name'=>'School A','code'=>'A','status'=>'active','timezone'=>'Asia/Kolkata']);
  $b=School::create(['name'=>'School B','code'=>'B','status'=>'active','timezone'=>'Asia/Kolkata']);
  $ua=User::create(['school_id'=>$a->id,'name'=>'Admin A','code'=>'AA','username'=>'admin-a','user_type'=>'admin','password'=>Hash::make('password')]);
  $ub=User::create(['school_id'=>$b->id,'name'=>'Admin B','code'=>'BB','username'=>'admin-b','user_type'=>'admin','password'=>Hash::make('password')]);
  DB::table('settings')->insert([['school_id'=>$a->id,'type'=>'school-a-only','description'=>'A','created_at'=>now(),'updated_at'=>now()],['school_id'=>$b->id,'type'=>'school-b-only','description'=>'B','created_at'=>now(),'updated_at'=>now()]]);
  $this->actingAs($ua);
  $this->assertSame(['school-a-only'],Setting::pluck('type')->all());
  $created=Setting::create(['type'=>'created-by-a','description'=>'A']);
  $this->assertSame($a->id,$created->school_id);
  $this->assertNull(Setting::find(DB::table('settings')->where('type','school-b-only')->value('id')));
  $this->actingAs($ub);
  $this->assertEqualsCanonicalizing(['school-b-only'],Setting::pluck('type')->all());
 }
}
